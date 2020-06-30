# TeamBuilder

Simple Laravel application bootstrapped with Valet.

___


### Usage

- Navigate to root path in browser and view landing page.

___


### Special Features

- You can add "team_sizes[]" or "min_goalies" as query parameters in the page URL to adjust the constraints on the algorithm.

- Added additional test to split up the combined test.

___


### Overview

##### Migration

- Users table, no auth.
___

##### Seeder

- Data from .sql, I decided make a seeder and just slap it in there instead of run the file itself.
___

##### User Model and Eloquent ORM

- Standard eloquent stuff, used some scopes for semantic building.
___

##### LandingController 

- Handles the view.
___

##### Testing with provided document

- Added an additional test to split up the rather large one.
___

##### View

- Yields responsive tables representing the teams and players. Highlights goalies, and tracks statistics.
___
