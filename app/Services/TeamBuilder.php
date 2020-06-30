<?php

namespace App\Services;

use Debugbar;
use Str;
use Faker\Factory as Faker;

use App\User;

class TeamBuilder {

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

  public function buildTeams($options) {
    $minimumGoalies = $options['minimum_goalies'];
    $teamSizeRange = $options['sizes'];

    $tempTeams = [];
    $playersCount = User::total();

    usort($this->players, function ($a, $b) {
      return $a['ranking'] - $b['ranking'];
    });

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

    $readyKeys = array_keys($readyRaw);
    $ready = [];
    for ($i = 0; $i < count($readyRaw); $i++) {
      $ready[$i] = $readyRaw[$readyKeys[$i]];
    }

    $notReadyRaw = array_filter($tempTeams, function ($tempTeam) use ($minimumGoalies) {
      return count($tempTeam['goalie_indices']) < $minimumGoalies;
    });

    $notReadyKeys = array_keys($notReadyRaw);
    $notReady = [];
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
          $swapPlayer = array_reduce($notReady[$n]['users'], function ($result, $item) use ($swapGoalieRanking) {
            return $item['ranking'] === $swapGoalieRanking ? $item : $result;
          });
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
