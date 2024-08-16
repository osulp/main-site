# Template for site-specific migrations

Copy this directory and rename `template` with a short name of the site.

Migrations specific to that site will be included here. Any custom Source/Process plugins can be created and referenced
here.

## Checklist

It is recommended to create lists here for the site and update them during the migration process.

### Example List

#### Node types with Paragraph fields

Listing the dependencies of the type can help you understand what needs to be configured for the migration.

- [ ] New type
    - Uses Paragraphs
    - Uses Image
    - Uses File Uploads
- [x] Done type

#### Group Types

If a node needs to be included in a Group or be Group aware a custom migration is needed for those types other than
Basic page and Parent Unit

- [ ] Program
- [ ] Degrees
