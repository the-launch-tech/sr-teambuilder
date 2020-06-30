<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Team Builder</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo asset('css/app.css')?>" type="text/css">
    <script  href="<?php echo asset('js/app.css')?>"></script>
  </head>
  <body>
    <header id="header" class="header">
      <h2 class="m-0  text-center text-uppercase font-weight-bolder">Team Builder</h2>
      <div class="py-2 px-4">
        <p class="text-center">Optionally pass "team_sizes[]" or "min_goalies" as query parameters to the URL to adjust the constrains on the algorithm.</p>
      </div>
    </header>
    <main id="page" class="page flex-center position-ref full-height">
      <div class="container-fluid pl-4 pr-4">
        <div class="row d-flex flex-wrap align-items-stretch">
          @if (count($teams) > 0)
            @foreach ($teams as $team)
              <div class="col-lg mb-5">
                <div class="card h-100">
                  <div class="card-body p-0">
                    <h3 class="text-center text-uppercase font-weight-bold">{{ $team['name'] }} <span class="badge badge-pill badge-success">{{ $team['team_rank'] }}</span></h3>
                    <table class="table table-hover" style="width: 100%;">
                      <thead class="thead-light">
                        <tr>
                          <th class="font-weight-bold" scope="col">#</th>
                          <th class="font-weight-bold" scope="col">Rank</th>
                          <th class="font-weight-bold" scope="col">First Name</th>
                          <th class="font-weight-bold" scope="col">Last Name</th>
                          <th class="font-weight-bold" scope="col">Position</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php $counter = 1; @endphp
                        @foreach ($team['players'] as $player)
                        <tr class="{{ $player['can_play_goalie'] ? 'table-info' : '' }}">
                          <th class="font-weight-light" scope="row">{{ $counter }}</th>
                          <td class="font-weight-bold"><span class="badge badge-pill badge-info text-white">{{ $player['ranking'] }}</span></td>
                          <td class="text-truncate font-weight-light">{{ $player['first_name'] }}</td>
                          <td class="text-truncate font-weight-light">{{ $player['last_name'] }}</td>
                          <td class="text-truncate font-weight-light">{{ $player['can_play_goalie'] ? 'Goalie' : 'Field' }}</td>
                        </tr>
                        @php $counter++; @endphp
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            @endforeach
          @else
            <div class="h-100 d-flex justify-center align-center p-5">
              <h3 class="text-center text-uppercase font-weight-bolder">Uh Oh! No Teams!</h3>
            </div>
          @endif
        </div>
      </div>
    </main>
    <footer id="footer" class="footer">
      <span>©2020 Daniel Griffiths | TeamBuilder</span>
    </footer>
  </body>
</html>
