(function () {
  CKEDITOR.plugins.add("osu_ckeditor_plugins_osu_buttons", {
    icons: "osu_buttons",
    init: function (editor) {
      // Add plugin CSS to allow icons to be rendered in current editor.
      const cssPath = `${this.path}styles/osu_buttons.css`;
      editor.addContentsCss(cssPath);
      CKEDITOR.document.appendStyleSheet(cssPath);

      editor.addCommand(
        "osu_buttons",
        new CKEDITOR.dialogCommand("osu_buttonsDialog")
      );
      editor.ui.addButton("osu_buttons", {
        label: "Button Picker",
        command: "osu_buttons",
        toolbar: "osu_buttons"
      });

      if (editor.contextMenu) {
        editor.addMenuGroup("osu_buttonsGroup");
        editor.addMenuItem("osu_buttonsItem", {
          label: "Add a button",
          icon: `${this.path}icons/osu_buttons.png`,
          command: "osu_buttons",
          group: "osu_buttonsGroup"
        });
        editor.contextMenu.addListener(function(element) {
          // only targeted on right click of an existing item
          if (element.getAscendant("osu_buttons", true)) {
            return { osubuttonsItem: CKEDITOR.TRISTATE_OFF };
          }
          return null;
        });
      }

      CKEDITOR.dialog.add(
        "osu_buttonsDialog",
        `${this.path}dialogs/osu_buttons.js`
      );
    }
  });
} ());
