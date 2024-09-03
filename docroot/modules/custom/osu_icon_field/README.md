# OSU Icon Field

Custom Drupal field for osu and fontawesome icons

## Compile JS

To compile once \

```shell
npm run build
```

To continuously compile \

```shell
npm run watch
```

## Icon Name updates

### OSU Icon Names

To Generate a new list of OSU Icons, replace osu-icon-url with the latest url.

```shell
npm run generate-icons osu-icon-url
```

### FA Icons

There are two lists of FA icons we need to generate, the normal solid ones and the branded ones. Replace the 
`<version>` with the appropriate version to generate for both.

```shell
npm run generate-fa-icons https://cdnjs.cloudflare.com/ajax/libs/font-awesome/<version>/css/fontawesome.min.css
```

```shell
npm run generate-fa-icons https://cdnjs.cloudflare.com/ajax/libs/font-awesome/<version>/css/brands.min.css
```

Copy the results into the respective arrays in [icon-list.js](js/icon-list.js)
