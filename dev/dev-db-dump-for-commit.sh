#!/bin/sh

docker compose run --rm apache drush sql:dump > dev/mariadb-init/default.sql