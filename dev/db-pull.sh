#!/bin/sh

PS3='Would you like to dump production or staging? '
environment="drupal-test"
options=("Prod" "Staging")
select opt in "${options[@]}"
do
  case $opt in
    "Prod")
      echo "Setting dump environment to production"
      environment="drupal-prod"
      break ;;
    "Staging")
      echo "Setting dump environment to staging"
      break ;;
    *) echo "invalid option $REPLY";;
  esac
done
echo

# Find pod to pull files from
pod=`kubectl get pods -n $environment --no-headers -o custom-columns=":metadata.name" | grep -G ^library-php | head -n 1`
# Local database dump file
out_file="dev/mariadb-init/live_dump.sql"
# Offending COLLATE text
to_replace="COLLATE=utf8mb4_0900_ai_ci"
# Script path
parent_path=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )

# Start by running in the script directory
cd "$parent_path/.."

# Dump the database
kubectl exec -itn $environment $pod -- bash -c "drush sql:dump --result-file=/tmp/dump.sql --extra-dump=--no-tablespaces"

# Pull the database
kubectl cp $environment/$pod:/tmp/dump.sql $out_file

# Replace COLLATE text
sed -i '' "s/$to_replace//g" $out_file

PS3=$'Would you like to destroy you local environment and sync to the pulled environment?\nWarning: all local configuration changes (including those made to solr) will be destroyed '
options=("Yes" "No")
select opt in "${options[@]}"
do
  case $opt in
    "Yes")
      echo "Removing local environment"
      docker compose down -v
      break ;;
    "No")
      echo "No changes necessary, exiting"
      break ;;
    *) echo "invalid option $REPLY";;
  esac
done
