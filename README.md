# Key Value Store
This project is created from the requirements set by [this](https://github.com/nedbase/code-challenge-key-value-storage) code-challenge as interview process by NedBase.

## Installation
Clone this repository in a folder of your linking.

## Running commands
The available commands are possible to run using php. This can either be done by a local instance of PHP, or the `docker-compose` image that is provided in the repository
The minimum version of PHP is 8.1.

### Available Commands
| Commands | Description | Arguments | Example | 
| ------------- |:-------------:|:-------:| -----:|
| SET | Set a key to the given value | Key : string, value : mixed | `php ./bin/console SET x 1` | 
| GET | Get a value by the given key | Key : string | `php ./bin/console GET x` | 
| DEL | Delete a value by the given key | Key : string | `php ./bin/console DEL x` |
| START | Start a transaction | None | `php ./bin/console START` |
| COMMIT | Commit a transaction | None | `php ./bin/console COMMIT` |
| ROLLBACK | Rollback a transaction | None | `php ./bin/console ROLLBACK` |

## Running tests
Running the tests that are located in `tests`
Run the following using php:
`php ./bin/phpunit`

