<?php

namespace App\Services;

use Debugbar;
use Str;
use Faker\Factory as Faker;

use App\User;

class TeamBuilder {

  public function __construct($users) {
    $this->faker = Faker::create();
    $this->teams = [];
    $this->users = json_decode(json_encode($users), true);
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
    $sizes = $options['sizes'];

    $teams = [];
    $totalUsers = User::total();

    usort($this->users, function ($a, $b) {
      return $a['ranking'] - $b['ranking'];
    });

    $closest = [null, null];
    foreach ($sizes as $size) {
      if (!$closest[0] || $totalUsers % $size < $closest[1]) {
        $closest = [$size, $totalUsers % $size];
      }
    }

    $totalTeams = intdiv($totalUsers, $closest[0]);

    $count = 1;
    foreach ($this->users as $user) {
      $teamIndex = $count % $totalTeams;
      if (!isset($teams[$teamIndex])) {
        $teams[$teamIndex] = [
          'users' => [],
          'goalie_indices' => [],
        ];
      }
      $teams[$teamIndex]['users'][] = $user;
      if ($user['can_play_goalie']) {
        $teams[$teamIndex]['goalie_indices'][] = count($teams[$teamIndex]['users']) - 1;
      }
      $count++;
    }

    $unorderedReadyTeams = array_filter($teams, function ($team) use ($minimumGoalies) {
      return count($team['goalie_indices']) >= $minimumGoalies;
    });

    $readyKeys = array_keys($unorderedReadyTeams);
    $readyTeams = [];
    for ($i = 0; $i < count($unorderedReadyTeams); $i++) {
      $readyTeams[$i] = $unorderedReadyTeams[$readyKeys[$i]];
    }

    $unorderedNotReadyTeams = array_filter($teams, function ($team) use ($minimumGoalies) {
      return count($team['goalie_indices']) < $minimumGoalies;
    });

    $notReadyKeys = array_keys($unorderedNotReadyTeams);
    $notReadyTeams = [];
    for ($i = 0; $i < count($unorderedNotReadyTeams); $i++) {
      $notReadyTeams[$i] = $unorderedNotReadyTeams[$notReadyKeys[$i]];
    }

    if (count($notReadyTeams) < 1) {
      $this->teams = array_map(function ($readyTeam) {
        return $readyTeam['users'];
      }, $readyTeams);
      return $this;
    }

    for ($n = 0; $n < count($notReadyTeams); $n++) {
      $added = false;
      for ($r = 0; $r < count($readyTeams); $r++) {
        if (!$added && isset($readyTeams[$r]) && count($readyTeams[$r]['goalie_indices']) > 1) {
          $goalie = $readyTeams[$r]['users'][$readyTeams[$r]['goalie_indices'][0]];
          $goalieRanking = $goalie['ranking'];
          $notReadyTeams[$n]['users'][] = $goalie;
          $notReadyTeams[$n]['goalie_indices'][] = count($notReadyTeams[$n]['users']) - 1;
          $replacementWithRank = array_reduce($notReadyTeams[$n]['users'], function ($result, $item) use ($goalieRanking) {
            return $item['ranking'] === $goalieRanking ? $item : $result;
          });
          array_splice($readyTeams[$r]['users'], $readyTeams[$r]['goalie_indices'][0], 1);
          array_splice($readyTeams[$r]['goalie_indices'], 0, 1);
          $readyTeams[$r]['users'][] = $replacementWithRank;
          $readyTeams[] = $notReadyTeams[$n];
          $added = true;
        }
      }
    }

    $this->teams = array_map(function ($readyTeam) {
      return $readyTeam['users'];
    }, $readyTeams);

    return $this;
  }
}
