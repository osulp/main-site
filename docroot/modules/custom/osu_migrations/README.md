# OSU Migrations

Contains submodules for D7 to D9 migrations

## OSU Migrations Files

To properly migrate files open up the upgrade_d7_files.yml and set the source_base_path to either the public url of the
drupal or set the file path to the root of the distro.

Currently, it is set to the base of this installation so one could copy the files into the container and run the
migration.

## Migrations:

Migrations need to be run in a specific order.

1. ### User Accounts

   This runs every migration require to bring in user accounts and their CAS account linking.

   `drush migrate:import --tag='OSU Accounts'`

2. ### Media
   Migrate all the Files and Media entities.
   Ensure that you first update the `upgrade_d7_file_private.yml` file to include the correct path to the private files
   directory. Do not commit this change.

   `drush migrate:import --tag='OSU Media'`

3. ### Taxonomy Tags
   `drush migrate:import --tag='OSU Taxonomy'`

4. ### Migrate Custom Block Content
   We need to migrate all the custom block contents first before paragraphs as we use block content in the paragraph
   migration, and we want to ensure all the original ID's of the blocks are there first.

   `drush migrate:import --tag='OSU Custom Blocks'`
5. ### Paragraphs to Layout Builder
   Paragraphs to Layout Builder migration requires two steps:

    1. #### Run all the Paragraphs to LB migrations first.
       `drush migrate:import --tag='OSU Paragraphs'`

    2. #### Run all the content migrations that used Paragraphs.
       `drush migrate:import --tag='Layout content' --force`

6. ### Feature Story/Articles to Story
   `drush migrate:import --tag='Feature Story'`

7. ### Other Content Types and Content Migrations
   To import other Content types, fields, Widget settings and nodes

   `drush migrate:import --tag='OSU Configuration' --force`

   Importing the content after all the Node types, Fields, and displays modes are migrate.

   `drush migrate:import --tag='OSU Content' --force`

   **Custom Content types** there will be times when fields that are accosiated to a Node Bundle use:

    * File/Image reference
    * Taxonomy Term Reference
    * Entity Reference
    * Text Area (other than Body field)

   With these fields you will need to write a custom Migration YAML for each node type and manually set the Lookup
   Plugins for References and use the `osu_media_wysiwyg_filter` plugin on the Text Area fields to ensure that embedded
   media is updated to use the new Drupal embedding system. See Site [Specific Migrations](#site-specific-migrations)
   and [Custom Content Types](#custom-content-types) for more information.

8. ### Views
   `drush migrate:import d7_views_migration`

   If you have a list of views ID's you only want to migrate you can do that with:

   `drush migrate:import d7_views_migration --idlist=events,event_list`

9. ### Site Specific Migrations
   Create a new module under site_migrations and name it 'osu_migrations_site' where 'site' is some short version of the
   site working with. This will contain any configurations and migrations for this specific site that would not be
   migrations that the rest of the distribution would go.

   Enable your new module and run any of the migrations you define.

10. ### Migrate Groups

    Associate different Nodes with their Groups and create menu entries.
    `drush migrate:import --tag='OSU Groups'`
    For Custom node Bundles that are Associated to Organic Groups in Drupal 7
    see [Custom Content Types in Groups](#custom-content-types-in-groups)

11. ### Migrate the URL Aliases and Redirects
    `drush migrate:import --tag='OSU Alias'`

    `drush migrate:import --tag='OSU Redirect'`

12. ### The Last content migration to run is Users to Profiles
    `drush migrate:import --tag='OSU Drupal Profile'`

13. ### Menus
    `drush migrate:import --tag='OSU Menus'`

14. ### Block Placement
    Migrate all block placements.

    `drush migrate:import --tag='OSU Blocks'`

## Custom Content Types

Each Content type that has a Reference Field and/or a Text Area field (other than body) will require custom Migrations
for the data. You can use the Migration tag `OSU Configuration` to have Drupal create the Content types and Fields. Any
Taxonomy Term Reference, Entity Reference fields need to have their Bundles updated to match what was configured in
Drupal 7. Inside the `site_migrations` folder you will find previous migrations that required custom migration YAML's,
use them for examples.

### Custom Content Types in Groups

For Node types that were added to Organic Groups (Organic Group Content) a separate migration YAML is required to
associate the new nodes with groups. Firstly you need to "Install" Groups on the Custom Content type. Then using the
Machine name generated for this Group association the Migration YAML can be set to add the nodes to the Group.
Proper order should be respected, Nodes need to be migrated first Then the group Association Migration. Check
out `site_migrations` For examples of Group Content migration.

### Custom Content Types with Paragraph Fields.

If the Paragraphs Module was used on any other Node bundle other than Basic Page and Book Page you must create a custom
YAML migration for this. Checkout the `site_migrations` folder for past migrations to use as examples.
