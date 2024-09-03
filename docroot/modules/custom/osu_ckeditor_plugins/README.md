# OSU CKEDITOR Plugins

## Development Requirements
- nodejs
- npm
- npx

We use eslint with the drupal extension for all JavaScript files.

Install all dependencies 
`npm install`

## Update Fontawesome Icons
1. To update the icons download the latest from [fontawesome](https://fontawesome.com/download) Free for Web.
2. Extract the zip webfonts directory into js/plugin/osu_icons/styles/webfonts.
3. Extract icons.json from metadata and place into js/plugins/osu_icons/dialogs
4. Extract the scss into js/plugin/osu_icons/styles
   1. Rename brands.scss to _brands.scss
   2. Rename regular.scss to _regular.scss
   3. Rename solid.scss to _solid.scss
   4. Rename v4-shims.scss to _v4-shims.scss
   5. Rename fontawesome.scss to _fontawesome.scss
   6. Ensure font path in _variables.scss `"./webfonts" !default;`
   7. Ensure all renamed scss files are loaded in _fontawesome
5. run gulp
