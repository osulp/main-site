#!/bin/bash

PS3='What would you like to update?: '
options=("Dry-run" "Dev" "Prod" "Quit")
select opt in "${options[@]}"
do
    case $opt in
        "Dry-run")
            echo "Performing update dry-run"
            docker compose run --rm apache composer update -o --dry-run
            ;;
        "Dev")
            echo "Running composer update"
            docker compose run --rm apache composer update -o --dev
            ;;
        "Prod")
            echo "Running composer update --no-dev"
            docker compose run --rm apache composer update -o --no-dev
            ;;
        "Quit")
            break
            ;;
        *) echo "invalid option $REPLY";;
    esac
    REPLY=
done
