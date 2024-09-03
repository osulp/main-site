# Template for site-specific migrations

## Checklist

It is recommended to create lists here for the site and update them during the migration process.

### Example List

#### Node with Custom Migrations

- [ ] Personal Profile
    - Images
    - Links
- [x] News Release
    - Images
- [x] OSU in the news
- [x] OSU Today
-

#### Node types with Paragraph fields

Listing the dependencies of the type can help you understand what needs to be configured for the migration.

- [x] Story
    - Uses Paragraphs
    - Uses Image
    - Uses Term references

#### Group Types

If a node needs to be included in a Group or be Group aware a custom migration is needed for those types other than
Basic page and Parent Unit

- [ ] News Release
- [ ] Story
- [ ] OSU Today
- [ ] OSU in the News
