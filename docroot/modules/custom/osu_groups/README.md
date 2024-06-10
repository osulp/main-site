Running tests

* Launch the dev version of osu-wams/drupal container 
* Exec into container 
* Add modules that other tests require to run
```shell
composer require --dev drupal/search_api drupal/entity_browser
```
* Copy phphunit.xml
```shell
cp /var/www/html/phpunit.xml /var/www/html/docroot/core/
 ```
* Move into the Core directory
* To Run the Specific Test
```shell
phpunit -c . ../modules/custom/osu_groups/modules/osu_groups_basic_group/
```
