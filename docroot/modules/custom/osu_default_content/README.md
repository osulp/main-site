# OSU Default Content

To Export everything needed for a Section Library Item you will need to
know the ID of the library item. Remove any dependency on the User that
created the media item. Will be under the _meta definition in every default
yaml.

Set the default user to be user id of `1` for every YAML.

## Adding to Sites

Each new template will require a submodule in order for the Default Content
module to add the data to existing sites. If
you use any Media inside your sections/blocks you must create a .module
file and implement the
function `hook_modules_installed($modules, $is_syncing)` and create an if
block to check that this new submodule is
being installed.

```php
 if (in_array('module_name', $modules) && !$is_syncing)
```

This will ensure that we only run our code Once.

a Service has been written to facilitate updating the media ID of the image
used in the Layout Background of either the
Section or the Block. It takes two parameters and the second one is
optional.

```php
 \Drupal::service('osu_default_content.library_media_update')::updateSectionLibrarySectionBackground();
```

The first parameter should be the UUID of the Section Library Template. If
you Background image is applied to the
Section then this is all that is needed.

For the use when the Section Library Template has a Block that has the
Background set there then you must provide that
Blocks UUID from the section. You can find this inside the YAML that was
generated from default-content:
export-references.

## Examples

Exporting a Media item, and it's file to a folder where the ID of the media
item is `31`

```shell
drush default-content:export-references media 31 --folder=/tmp/media-export
```

Exporting a Section Library Template with the id of `1`

```shell
drush default-content:export-references section_library_template 1 \ 
--folder /tmp/section_library_item

```
