(function (Drupal) {
  CKEDITOR.plugins.add("osu_ckeditor_plugins_osu_icons", {
    icons: "osu_icons",
    init: function (editor) {
      CKEDITOR.dtd.$removeEmpty.span = 0;
      // Add plugin CSS to allow icons to be rendered in current editor.
      const cssPath = `${this.path}styles/osu_icons.css`;
      const osuIconUrl =
        'https://cdn.icomoon.io/155267/OregonStateBrandIcons/style-cf.css?8rsof6';

      editor.addContentsCss(cssPath);
      CKEDITOR.document.appendStyleSheet(cssPath);

      editor.addContentsCss(osuIconUrl);
      CKEDITOR.document.appendStyleSheet(osuIconUrl);

      editor.addCommand(
        "osu_icons",
        new CKEDITOR.dialogCommand("osu_iconsDialog")
      );
      editor.ui.addButton("osu_icons", {
        label: "Icon picker",
        command: "osu_icons",
        toolbar: "osu_icons"
      });

      if (editor.contextMenu) {
        editor.addMenuGroup("osu_iconsGroup");
        editor.addMenuItem("osu_iconsItem", {
          label: "Add an icon",
          icon: `${this.path}icons/osu_icons.png`,
          command: "osu_icons",
          group: "osu_iconsGroup"
        });
        editor.contextMenu.addListener(function(element) {
          // only targeted on right click of an existing item
          if (element.getAscendant("osu_icons", true)) {
            return { osuiconItem: CKEDITOR.TRISTATE_OFF };
          }
          return null;
        });
      }

      CKEDITOR.dialog.add(
        "osu_iconsDialog",
        `${this.path}dialogs/osu_icons.js`
      );
    }
  });
} (Drupal));
