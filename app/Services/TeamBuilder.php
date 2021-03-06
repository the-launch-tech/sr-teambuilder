<?php

namespace App\Services;

use Debugbar;
use Str;
use Faker\Factory as Faker;

use App\User;

class TeamBuilder {

  public $teams = [];
  private $faker;
  private $players;

  public function __construct($players) {
    $this->faker = Faker::create();
    $this->teams = [];
    $this->players = json_decode(json_encode($players), true);
  }

  public function generateMeta() {
    $finalStructure = [];
    foreach ($this->teams as $team) {
      $finalStructure[] = [
        'name' => $this->faker->country . ' | ' . Str::title($this->faker->word),
        'players' => $team,
        'team_rank' => array_sum(array_map(function ($player) { return $player['ranking']; }, $team))
      ];
    }
    return $finalStructure;
  }

  /**
  * 1. Take options for algorithm constraints
  * 2. Use modulo to help find most even distribution of teams
  * 3. Use modulo and sorted list to help evenly distribute rankings across tempTeams
  * 4. Validate that each team has a goalie
  * 4. If some teams do not, try to swap player of similar rank for duplicate goalie on other team
  * 5. Set valid teams property and return instance
  *
  * Notes: Try to maintain a generally O(n) runtime so that if the data was big performance wouldn't suffer.
  * The last swapping section could be refactored a bit, it's context isn't one of huge lists (ie. list of not ready teams, rather than all players or something)
  */
  public function buildTeams($options) {
    $minimumGoalies = $options['minimum_goalies'];
    $teamSizeRange = $options['sizes'];

    $tempTeams = [];
    $playersCount = User::total();

    $closestTotalPlayers = [null, null];
    foreach ($teamSizeRange as $sizeInRange) {
      if (!$closestTotalPlayers[0] || $playersCount % $sizeInRange < $closestTotalPlayers[1]) {
        $closestTotalPlayers = [$sizeInRange, $playersCount % $sizeInRange];
      }
    }

    $totalTeams = intdiv($playersCount, $closestTotalPlayers[0]);

    $n = 1;
    foreach ($this->players as $player) {
      $i = $n % $totalTeams;
      if (!isset($tempTeams[$i])) {
        $tempTeams[$i] = [
          'users' => [],
          'goalie_indices' => [],
        ];
      }
      $tempTeams[$i]['users'][] = $player;
      if ($player['can_play_goalie']) {
        $tempTeams[$i]['goalie_indices'][] = count($tempTeams[$i]['users']) - 1;
      }
      $n++;
    }

    $readyRaw = array_filter($tempTeams, function ($tempTeam) use ($minimumGoalies) {
      return count($tempTeam['goalie_indices']) >= $minimumGoalies;
    });

    $ready = [];
    $readyKeys = array_keys($readyRaw);
    for ($i = 0; $i < count($readyRaw); $i++) {
      $ready[$i] = $readyRaw[$readyKeys[$i]];
    }

    $notReadyRaw = array_filter($tempTeams, function ($tempTeam) use ($minimumGoalies) {
      return count($tempTeam['goalie_indices']) < $minimumGoalies;
    });

    $notReady = [];
    $notReadyKeys = array_keys($notReadyRaw);
    for ($i = 0; $i < count($notReadyRaw); $i++) {
      $notReady[$i] = $notReadyRaw[$notReadyKeys[$i]];
    }

    if (count($notReady) < 1) {
      $this->teams = array_map(function ($readyTeam) { return $readyTeam['users']; }, $ready);
      return $this;
    }

    for ($n = 0; $n < count($notReady); $n++) {
      $swapped = false;
      for ($r = 0; $r < count($ready); $r++) {
        if (!$swapped && isset($ready[$r]) && count($ready[$r]['goalie_indices']) > 1) {
          $swapGoalie = $ready[$r]['users'][$ready[$r]['goalie_indices'][0]];
          $swapGoalieRanking = $swapGoalie['ranking'];
          $notReady[$n]['users'][] = $swapGoalie;
          $notReady[$n]['goalie_indices'][] = count($notReady[$n]['users']) - 1;
          $swapPlayer = false;
          while (!$swapPlayer && $swapGoalieRanking > 0) {
            $swapPlayer = array_reduce($notReady[$n]['users'], function ($result, $item) use ($swapGoalieRanking, $swapGoalie) {
              return $item['ranking'] === $swapGoalieRanking && $item['id'] !== $swapGoalie['id'] ? $item : $result;
            });
            $swapGoalieRanking--;
          }
          array_splice($ready[$r]['users'], $ready[$r]['goalie_indices'][0], 1);
          array_splice($ready[$r]['goalie_indices'], 0, 1);
          $ready[$r]['users'][] = $swapPlayer;
          $ready[] = $notReady[$n];
          $swapped = true;
        }
      }
    }

    $this->teams = array_map(function ($readyTeam) { return $readyTeam['users']; }, $ready);

    return $this;
  }
}
