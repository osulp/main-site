#!/bin/bash

docker compose exec apache drush sql:dump > dev/mariadb-init/default.sql