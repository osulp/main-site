# Division of Research and Innovation Migrations

## Custom paragraph bundles

Research has the following paragraphs bundles that will require custom migrations:

- [X] Grid (grid)
- [x] Image Grid (par_image_grid)
- [x] Image Grid Cell (par_image_grid_cell)

Paragraph field is only used on Basic page, Book page, and Parent unit. Image Grid has a field Image Grid Cell which
maps to the Image Grid Cell paragraph bundle

## Media Fields

The following content types have a reference to an image/document that will require custom migrations

- [x] Impact Story
- [x] ORIN_Participant
- [X] Sidebar Carousel

## Views known to be used

- content_plus
- impact_stories
- ipdeadlines
- limited_submission
- orin_list_participant
- research_news_block
- tools_and_services

## Content Updates

Any term or entity reference field should be checked over to ensure the correct bundles are configured on the form.

The Organization Index single node doesn't have a corresponding field collection.
