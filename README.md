# Oregon State University Libraries Drupal

This is the Drupal site for the [main library website](https://library.oregonstate.edu), based on the main OSU Drupal Distribution.

## Update workflow

To update drupal or any of the modules, choose a method below:

Note: Make sure to get the latest code before attempting either of these `git pull`

### Automated (Recommended)

Requirements:

1. Github [Personal Access Token](https://github.com/settings/tokens) for composer to access custom repositories.
1. Composer auth tokens in default install location: `~/.composer/auth.json`. Otherwise update `docker-compose.yml`

Process:

1. Run `dev/update.sh`
1. You will be given several update options:
   - Dry-run: run `compose update -o --dry-run` to see which modules have updates
   - Dev: run `compose update -o --dev` to install updates for the development environment
   - Prod: run `compose update -o --no-dev` to install updates for the production environment
1. Your `composer.lock` file will update according to your `composer.json` file and selection
1. Then you can commit the changed `composer.json` and `composer.lock` and push

### Manual

1. Enter the container `docker compose exec apache bash`
1. Check for Drupal library & modules updates `composer outdated drupal/\*`
1. If you want to only check for packages that have minor version updates
1. `composer outdated -m drupal/\*`
1. To check for only updates that are required by this distribution's composer.json
1. `composer outdated -D drupal/\*`
1. Get updates:
   - To update a specific package only
     - `composer update vendor/package`
     - eg `composer update drupal/my_module`
   - Update only core and it's dependencies
     - `composer update drupal/core-composer-scaffold drupal/core-recommended drupal/core-dev --with-all-dependencies`
   - Get all updates
     - `composer update`
1. Commit the changed composer.json and composer.lock and push

## Local Development

### Build the container locally:

#### Easy:

- `docker compose build`

#### Advanced:

- For the development version of the container:
  - `docker build --target=development --tag=<tag> .`
- For the Production version
  - `docker build --target=production --tag=<tag> .`

### Useful docker commands:

- Start the containers in detached mode (you retain access to the terminal)
  - `docker compose up -d`
- Open a terminal in the Apache container
  - `docker compose exec apache bash`
- View the Apache/PHP logs
  - `docker compose logs -f apache`
- Stop the containers
  - `docker compose down`
- Stop the containers and remove any database config and solr data:
  - `docker compose down -v`

### Setup for theme or module development

- Copy `docker-compose.override.example.yml` to `docker-compose.override.yml`
- Replace `volumes` definitions with overrides in the format of: `/path/to/local/folder:/path/to/container/folder`. The first definition has the correct path to the container theme folder already set.
- Remove any other example definitions

## Environment Variables

### The Main variables for the Drupal Site.

- DRUPAL_DBNAME
  - The Database Name to use to setup the drupal and mariadb containers.
- DRUPAL_DBUSER
  - The Database User with permissions to that Database.
- DRUPAL_DBPASS
  - The Password to the Database User.
- DRUPAL_DBHOST
  - The Host name that the Database Server is running on

#### Optional Parameters

- DRUPAL_DBPORT
  - The Port the Database Server is running on
    - Default: 3306
- PRIVATE_FILE_PATH
  - The absolute file path to where private files are stored in the **_container_**. This should NOT be in webroot
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

## Importing the production database (OSU Library Developers only)

- Delete or rename the existing default database (`dev/mariadb-init/default.sql`) such that it's not a sql file (`default.sql.tmp`)
- Run the `dev/db-pull.sh` script to dump and pull the live database into `dev/mariadb-init/live_dump.sql`
- Delete the existing `mariadb` database: `docker compose down -v` (This will also delete the Solr index)
- Stop and start all containers
- Wait for `mariadb` to finish importing the database (`docker compose logs -f mariadb`)
- Visit local site and confirm import
- You will be missing all public & private files from production. This can later be fixed with a new scipt or the `stage_file_proxy` module

## Beavernetes (Kubernetes) deployment information:

OSULP uses a `kubernetes` backed deployment infrastructure we delightfully named `beavernetes`

### Deployed Images

- Apache/PHP - Built - Based on ghcr.io/osu-wams/php:8.2-apache which is eventually based on the official php:8.2-apache
- wodby/mariadb:10.11-3.28.3 - Database. It can be almost any database backend
- solr:8-slim - Powers search
- memcached:1.6-alpine - Will cache site when we can untangle it

### Required Environment Variables

- TEMP_FILE_PATH - Full path to temporary file folder in apache/php pod
- PRIVATE_FILE_PATH - Same, but for private files. This should be OUTSIDE of webroot
- LIBCAL_LOCATIONS - (for some reason) A single element array with a hash of {<libcal_location_id>: <location_name_lowercase>}
- LIBCAL_CLIENT_SECRET - Secret key for connecting to Libcal
- LIBCAL_CLIENT_ID - Our Libcal client ID
- DRUPAL_MEMCACHEHOST - Memcached host
- DRUPAL_MEMCACHE - true/false enable memcache
- DRUPAL_HASH_SALT - A salt value (Large "a-Z0-9" string)
- DRUPAL_DBHOST - Host name for database
- DRUPAL_DBPORT - Port for db host
- DRUPAL_DBUSER - User for db host
- DRUPAL_DBPASS - Pass for db host
- DRUPAL_DBNAME - Name of database
- DB_DRIVER - Type of driver to db

### Important Mounts

All of these would be in the apache/php deployment

- <PRIVATE_FILE_PATH> from env vars
- `/var/www/html/docroot/sites/default/files` The default path to servable public files
- `/etc/apache2/apache2.conf` path to apache config
- `/usr/local/etc/php/php.ini` path to php config
