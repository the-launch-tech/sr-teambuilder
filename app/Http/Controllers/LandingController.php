<?php

namespace App\Http\Controllers;

use Debugbar;

use App\User;
use App\Services\TeamBuilder;
use Illuminate\Http\Request;

class LandingController extends Controller {
  const DEFAULT_SIZES = [18, 19, 20, 21, 22];
  const DEFAULT_GOALIES = 1;

  public function getView(Request $request) {
    $sizes = $request->input('team_sizes') ?
      $request->input('team_sizes') :
      false;

    $minimumGoalies = $request->input('min_goalies') ?
      $request->input('min_goalies') :
      false;

    $users = User::getPlayers();

    $Builder = new TeamBuilder($users);

    $teams = $Builder->buildTeams([
        'sizes' => $sizes ? $sizes : self::DEFAULT_SIZES,
        'minimum_goalies' => $minimumGoalies ? $minimumGoalies : self::DEFAULT_GOALIES
      ])
      ->generateMeta();

    return view('landing', compact('teams'));
  }
}
