# Setup

## Requirements
1. Install [docker desktop](https://www.docker.com/products/docker-desktop)
1. Run `composer install` to install dependencies
1. Copy `web/sites/default/example.settings.php` to `web/sites/default/settings.php`
1. Update `web/sites/default/settings.php` by adding the D6 database stanza:
```PHP
$databases['drupal_old']['default'] = [
  'database' => getenv('DB_NAME'),
  'driver' => getenv('DB_DRIVER'),
  'host' => getenv('DB_HOST').'_old',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'password' => getenv('DB_PASSWORD'),
  'port' => getenv('DB_PORT'),
  'prefix' => '',
  'username' => getenv('DB_USER'),
];
```
And update the `config_sync_directory` definition:
```PHP
$settings['config_sync_directory'] = '../config/sync';
```
1. If restoring from a database dump, place D8 website `.sql` file in `dev/mariadb-init/mariadb` and D6 website `.sql` file in `dev/mariadb-init/mariadb_old`

## Configure domains
Add the following entries to your `/etc/hosts` file to access the services
```BASH
127.0.0.1   test.library.oregonstate.edu # Base service (drupal)
127.0.0.1   portainer.test.library.oregonstate.edu # Portainer
127.0.0.1   pma.test.library.oregonstate.edu # PHPMyAdmin
```

When more services are turned on in the `docker-compose.yml` file, more domains need to be configured to access them. Use the template:
`127.0.0.1  ${service_name}.test.library.oregonstate.edu # ${service_name}`

## Running the environment
`docker-compose up -d` Will start the the Drupal stack and make the website available at `http://test.library.oregonstate.edu:8000`

`docker-compose down` Will stop the Drupal stack and keep the database. Can be brought back up exactly where you left off.

`docker-compose down -v` Will stop the Drupal stack and remove volumes, destroying the database. Will need to reinstall drupal and all configuration or import a database dump