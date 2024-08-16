#!/bin/sh

PS3='What would you like to update?: '
options=("Dry-run" "Dev" "Prod" "Drupal Only" "Quit")
select opt in "${options[@]}"
do
  case $opt in
    "Dry-run")
      echo "Performing update dry-run"
      docker compose run --rm apache composer update -o --dry-run
      break
      ;;
    "Dev")
      echo "Running composer update"
      docker compose run --rm apache composer update -o
      break
      ;;
    "Prod")
      echo "Running composer update --no-dev"
      docker compose run --rm apache composer update -o --no-dev
      break
      ;;
    "Drupal Only")
      echo "Running composer update drupal/*"
      docker compose run --rm apache composer update -o drupal/*
      break
      ;;
    "Quit")
      break
      ;;
    *) echo "invalid option $REPLY";;
  esac
  REPLY=
done
