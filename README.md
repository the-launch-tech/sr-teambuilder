# TeamBuilder

Simple Laravel application bootstrapped with Valet.

___


## Usage

- Navigate to root path in browser and view landing page.

___


## Special Features

- You can add "team_sizes[]" or "min_goalies" as query parameters in the page URL to adjust the constraints on the algorithm.

- Added additional test to split up the combined test.

___


## Overview

#### Migration

- Users table, no auth.


#### Seeder

- Data from .sql, I decided make a seeder and just slap it in there instead of run the file itself.


#### User Model and Eloquent ORM

- Standard eloquent stuff, used some scopes for semantic building.


#### LandingController 

- Handles the view.


#### Testing with provided document

- Added an additional test to split up the rather large one.


#### View

- Yields responsive tables representing the teams and players. Highlights goalies, and tracks statistics.


#### Faker

- Country + Random Name


#### UI

- Bootstrap, totally static - no front end library.


