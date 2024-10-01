(function(Drupal) {
CKEDITOR.dialog.add("osu_iconsDialog", function(editor) {

  const e_class = `.${CKEDITOR.currentInstance.name}`;

  let icon_size = "fa-3x";
  let icon_color = "primary";
  let icon_chosen = "";

  // we use this function to wrap our on keyup code for triggering the filter of font awesome icons
  // the idea here is that we don't want to update which icons should be visible until there is a brief pause
  // in the typing.
  const delay = (function() {
    let timer = 0;
    return function(callback, ms) {
      clearTimeout(timer);
      timer = setTimeout(callback, ms);
    };
  })();

  // An associative array of fonts and an array of their search terms as a value.
  const fa_search_array = [];

  // An associative array. The keys are the font names, the values are their corresponding <i> value that we are using.
  const fa_style_xref = [];

  function icon_preview() {
    (function($) {
      $(`${e_class} #selected-icon`).empty();
      let icon_options = "";

      icon_options += $(`${e_class} #osu-icon-picker`).val();
      icon_options += ` ${$(`${e_class} #osu-icon-color`).val()}`;
      icon_options += ` ${icon_size}`;

      $(`${e_class} #selected-icon`).append(`<i class="${icon_options}"></i>`);
    })(jQuery);
  }

  function load_events() {
    (function($) {
      // Icon Color
      const colors = $(`${e_class} .osu-colors p`);

      colors.click(function(e) {
        colors.removeClass("color-active");
        const { className } = e.currentTarget;
        $(`${e_class} #osu-icon-color`).val(className);
        icon_color = className;
        icon_preview();
        $(this).addClass("color-active");
      });

      // Icon Size
      const sizes = $(`${e_class} .osu-sizes p`);
      sizes.click(function(e) {
        sizes.removeClass("size-active");
        const { className } = e.currentTarget;
        $(`${e_class} #icon-size`).val(className);
        icon_size = className;
        icon_preview();
        $(this).addClass("size-active");
      });

      $(`${e_class} #js-osu-icons-tab`).click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass("tab-active")) {
          $(this).addClass("tab-active");
          $(`${e_class} #js-fontawesome-icons-tab`).removeClass("tab-active");
          $(`${e_class} .osu-icons-wrapper`).addClass("icons-visible");
          $(`${e_class} .fontawesome-icons-wrapper`).removeClass(
            "icons-visible"
          );
        }
      });
      $(`${e_class} #js-fontawesome-icons-tab`).click(function(e) {
        e.preventDefault();
        if (!$(this).hasClass("tab-active")) {
          $(this).addClass("tab-active");
          $(`${e_class} #js-osu-icons-tab`).removeClass("tab-active");
          $(`${e_class} .fontawesome-icons-wrapper`).addClass("icons-visible");
          $(`${e_class} .osu-icons-wrapper`).removeClass("icons-visible");
        }
      });

      // Load OSU Icons from json file
      const osuIconUrl =
        "https://cdn.icomoon.io/155267/OregonStateBrandIcons/selection.json?6642o2";
      $.getJSON(osuIconUrl, function(data) {
        $.each(data.icons, function(key, val) {
          $(`${e_class} .osu-icons`).append(
            `<i class="icon-osu-${val.properties.name}"></i>`
          );
        });
      });

      // Load FontAwesome Icons from json file
      $.ajax({
        url: `${module_path}dialogs/icons.json`,
        dataType: "json",
        success(data) {
          // Iterates through keys of JSON object
          Object.keys(data).forEach(function(item) {
            // associative array of fonts and an array of their search terms as a value
            const fa_styles = data[item].styles;
            fa_search_array[item] = data[item].search.terms;
            fa_search_array[item].push(item);

            // some icons have 2 styles, they might have more in the future
            const fa_styles_length = fa_styles.length;

            /* Loops through all styles and generates the HTML and the
             * classes needed to output to screen.
             */
            for (let i = 0; i < fa_styles_length; i++) {
              let fa_class = `fa-${item}`;

              if (fa_styles[i] == "regular") {
                fa_class += " far";
              }
              if (fa_styles[i] == "brands") {
                fa_class += " fab";
              }
              if (fa_styles[i] == "solid") {
                fa_class += " fas";
              }

              $(`${e_class} .fontawesome-icons`).append(
                `<i class="${fa_class}"></i>`
              );

              // will use these values later when filtering

              fa_style_xref[item] = `<i class="${fa_class}"></i>`;
            }
          });
        }
      });

      // Search Functionality for FontAwesome icons
      $(`${e_class} #fontawesome-search-box`).keyup(function() {
        // this needs to be outside of the delay function or it never updates
        const filterValue = $(this).val();

        delay(function() {
          // removes all the icons from the screen
          $(`${e_class} .fontawesome-icons`).empty();

          Object.keys(fa_search_array).forEach(function(myFontTitle) {
            // myFontTitle is the name of a font awesome font

            let doesMatch = false;

            fa_search_array[myFontTitle].forEach(function(mySearchTerm) {
              // mySearchTerm is a search term used for the particular font

              // TODO: Review if we want to use include or look to see if the search term starts with the filter value
              if (mySearchTerm.includes(filterValue)) {
                // filterValue matches a search term, we want to display this font
                doesMatch = true;
              }
            });

            if (doesMatch) {
              $(`${e_class} .fontawesome-icons`).append(
                fa_style_xref[myFontTitle]
              );
            }
          });
        }, 300);
      });

      $(`${e_class} .osu-icons`).click(function(i) {
        const { className } = i.target;
        $(`${e_class} #osu-icon-picker`).val(className);
        icon_chosen = className;
        icon_preview();
      });

      $(`${e_class} .fontawesome-icons`).click(function(i) {
        $(`${e_class} #osu-icon-picker`).val(i.target.className);
        icon_chosen = i.target.className;
        icon_preview();
      });
    })(jQuery);
  }

  const build = `
<form class="${CKEDITOR.currentInstance.name}">
    <div id="selected-icon"></div>
    
    <div class="osu-colors">
        <label>Click on a color <input id="osu-icon-color" type="text" value="osu" readonly/></label>
        <p class="primary color-active"></p>
        <p class="light"></p>
        <p class="dark"></p>
        <p class="white"></p>
        <p class="pine"></p>
        <p class="luminance"></p>
        <p class="rogue-wave"></p>
        <p class="stratosphere"></p>
    </div>
    
    <div class="osu-sizes">
         <label>Click on a size <input id="icon-size" type="text" value="fa-3x" readonly/></label>
         <p class="fa-2x">S</p>
         <p class="fa-3x size-active">M</p>
         <p class="fa-5x">L</p>
         <p class="fa-7x">XL</p>
     </div>
     
     <div class="osu-icons-tabs">
         <a id="js-osu-icons-tab" class="tab-active" href="">OSU Icons</a>
         <a id="js-fontawesome-icons-tab" href="">Font Awesome Icons</a>
         <input id="osu-icon-picker" />
     </div>
     
     <div class="osu-icons-wrapper icons-visible">
         <div class="osu-icons"></div>
     </div>
     
     <div class="fontawesome-icons-wrapper">
        <label>Filter Icons</label><input id="fontawesome-search-box" type="text"/>
        <div class="fontawesome-icons"></div>
     </div>
     
     
</form>`;

  var { path: module_path } = CKEDITOR.currentInstance.plugins.osu_ckeditor_plugins_osu_icons;

  return {
    title: "OSU Icon picker",
    minWidth: 600,
    minHeight: 300,
    contents: [
      {
        id: "tab-basic",
        label: "Basic Settings",
        elements: [
          {
            type: "html",
            id: "osu_icon_tabs",
            html: build,
            onLoad() {
              load_events();
            }
          }
        ]
      }
    ],

    onShow() {
      this.insertMode = true;
      const selection = editor.getSelection();
      const element = selection.getStartElement();
      const e = element.$;
    },

    onOk() {
      let icon_classes = icon_color;
      icon_classes += ` ${icon_chosen}`;
      icon_classes += ` ${icon_size}`;

      const selection = editor.getSelection();
      const element = selection.getStartElement();
      const e = element.$;

      // If element has classes, lets keep them when rebuilding the link
      const classes = e.className ? `class="${e.className}"` : "";
      const icon = `&nbsp;<span class="${icon_classes}"></span>&nbsp;`;
      let content = icon;

      // Checks if it's a link
      if (e.tagName == "A") {
        console.log('is a link');
        content = `<a href="${e.href}" ${classes}> ${icon} ${e.innerHTML}</a>`;

        // ugly by e.prototype was coming up empty
        const e_proto = e.__proto__.__proto__.__proto__;

        // Create remove() function for IE11 and other crappy browsers
        if (!e_proto.hasOwnProperty("remove")) {
          e_proto.remove = function() {
            if (this.parentNode) {
              this.parentNode.removeChild(this);
            }
          };
        }

        // destroys previous element since it is getting replaced with content above
        e.remove();
      }

      // Adds the selected values into CKEditor
      if (this.insertMode) {
        console.log(content);
        editor.insertHtml(content, "unfiltered_html");
      }
    }
  };
});
}(Drupal));
