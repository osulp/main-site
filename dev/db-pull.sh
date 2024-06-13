#!/bin/bash

# Find pod to pull files from
POD=`kubectl get pods -n drupal-prod --no-headers -o custom-columns=":metadata.name" | grep -G ^library-php | head -n 1`
# Local database dump file
OUT_FILE="dev/mariadb-init/live_dump.sql"
# Offending COLLATE text
TO_REPLACE="COLLATE=utf8mb4_0900_ai_ci"
# Script path
PARENT_PATH=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )

# Start by running in the script directory
cd "$PARENT_PATH/.."

# Dump the database
kubectl exec -itn drupal-prod $POD -- bash -c "drush sql:dump --result-file=../dump.sql --extra-dump=--no-tablespaces"

# Pull the database
kubectl cp drupal-prod/$POD:/var/www/html/dump.sql $OUT_FILE

# Replace COLLATE text
sed -i '' "s/$TO_REPLACE//g" $OUT_FILE
