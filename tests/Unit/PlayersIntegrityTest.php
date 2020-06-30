<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\User;
use App\Services\TeamBuilder;

class PlayersIntegrityTest extends TestCase {

  /**
  * Check there are players that have can_play_goalie set as 1
  */
  public function testGoaliePlayersExist() {
    $goalieCount = User::isPlayer()
      ->canPlayGoalie('goalie')
      ->count();
		$this->assertTrue($goalieCount > 1);
  }

  /**
  * Check that there are at least as many players who can play goalie as there are teams
  */
  public function testAtLeastOneGoaliePlayerPerTeam() {
    $minGoalies = 1;
    $minValidLength = 18;
    $maxValidLength = 22;

    $users = User::getPlayers();

    $Builder = new TeamBuilder($users);

    $teams = $Builder->buildTeams([
      'sizes' => range($minValidLength, $maxValidLength),
      'minimum_goalies' => $minGoalies
    ])
    ->teams;

    $requiredLength = count($teams);
    $teamsWithGoalies = [];

    foreach ($teams as $team) {
      $added = false;
      foreach ($team as $player) {
        if (!$added && $player['can_play_goalie']) {
          $teamsWithGoalies[] = 1;
          $added = true;
        }
      }
    }

    $this->assertTrue(count($teamsWithGoalies) === $requiredLength);
  }

  /**
  * Calculate how many teams can be made so that there is an even number of teams and they each have between 18-22 players.
  */
  public function testPlayerCountWithinRange() : void {
    $minGoalies = 1;
    $minValidLength = 18;
    $maxValidLength = 22;

    $users = User::getPlayers();

    $Builder = new TeamBuilder($users);

    $teams = $Builder->buildTeams([
      'sizes' => range($minValidLength, $maxValidLength),
      'minimum_goalies' => $minGoalies
    ])
    ->teams;

    $requiredLength = count($teams);
    $teamsOfValidLength = [];

    foreach ($teams as $team) {
      if (count($team) >= $minValidLength && count($team) <= $maxValidLength) {
        $teamsOfValidLength[] = 1;
      }
    }

    $this->assertTrue(count($teamsOfValidLength) === $requiredLength);
  }

}
