# Oregon State University Libraries Drupal

This is based on the main OSU Drupal Distribution.

### Update workflow

1. Get the latest code `git pull`
1. Enter the container `docker compose exec apache bash`
1. Check for Drupal library & modules updates `composer outdated drupal/\*`
1. If you want to only check for packages that have minor version updates
1. `composer outdated -m drupal/\*`
1. To check for only updates that are required by this distribution's composer.json
1. `composer outdated -D drupal/\*`
1. Get updates

- To update a specific package only
  - `composer update vendor/package`
  - eg `composer update drupal/my_module`
- Update only core and it's dependencies
  - `composer update drupal/core-composer-scaffold drupal/core-recommended drupal/core-dev --with-all-dependencies`
- Get all updates
  - `composer update`

9. Commit the changed composer.json and composer.lock and push

## Local Development

Build the container locally:

- Easy: `docker compose build`
- Advanced:
  - For the development version of the container:
    - `docker build --target=development --tag=osuwams/drupal:9-apache-dev .`
  - For the Production version
    - `docker build --target=production --tag=osuwams/drupal:9-apache .`

Start the containers:

- `docker compose up -d`

View the logs:

- `docker compose logs -f apache`

Stop the containers:

- `docker compose down`

Stop the containers and remove any database config and solr data:

- `docker compose down -v`

## Environment Variables

### The Main variable for the Drupal Site.

- DRUPAL_DBNAME
  - The Database Name to use
- DRUPAL_DBUSER
  - The Database User with permissions to that Database.
- DRUPAL_DBPASS
  - The Password to the Database User.

#### Optional Parameters

- DRUPAL_DBHOST
  - The Host name that the Database Server is running on
    - Default: localhost
- DRUPAL_DBPORT
  - The Port the Database Server is running on
    - Default: 3306
- PRIVATE_FILE_PATH
  - The absolute file path to where private files are stored. This should NOT be in webroot
    - Default: '' (undefined & unused in drupal)

### Memcache Variables

- DRUPAL_MEMCACHE
  - The use of Memcache is enabled/disabled
- DRUPAL_MEMCACHEHOST
  - The host location for the memcache server. Default port will be used (11211)

### Environment Variables For Migrations

- DRUPAL_MIGRATE_DBNAME
  - The Database Name that contains the Source Data for the Migration
- DRUPAL_MIGRATE_DBUSER
  - The Database user with permissions to that Database
- DRUPAL_MIGRATE_DBPASS
  - The Password for the Database User

#### Optional Parameters

- DRUPAL_MIGRATE_DBHOST
  - The Host name that the Database Server is running on
    - Default: localhost
- DRUPAL_MIGRATE_DBPORT
  - The Port the Database Server is running on
    - Default: 3306

## Importing the production database

- Rename the existing default database (`dev/mariadb-init/default.sql`) such that it's not a sql file (`default.sql.tmp`)
- Run the `dev/db-pull.sh` script to dump and pull the live database into `dev/mariadb-init/live_dump.sql`
- Delete the existing `mariadb` database: `docker compose down -v` (This will also delete the Solr index)
- Stop and start all containers
- Wait for `mariadb` to finish importing the database (`docker compose logs -f mariadb`)
- Visit local site and confirm import
- You will be missing all public & private files from production. This can later be fixed with a new scipt or the `stage_file_proxy` module
