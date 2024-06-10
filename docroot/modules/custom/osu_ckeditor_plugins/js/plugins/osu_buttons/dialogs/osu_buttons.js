(function($) {
  CKEDITOR.dialog.add("osu_buttonsDialog", function(editor) {
    const editorId = CKEDITOR.currentInstance.name;

    let buttonText = "Button Text";
    let buttonSize = "";
    let buttonColor = "osu-btn-primary";
    let buttonLink = "";
    let buttonLinkTarget = "_blank";
    let buttonHtml = "";

    function preview() {
      $(`#${editorId} #selected-button`).empty();

      let buttonOptions = "btn";

      if (buttonSize) {
        buttonOptions += ` ${buttonSize}`;
      }

      if (buttonColor) {
        buttonOptions += ` ${buttonColor}`;
      }

      let btnText = buttonText;
      if (buttonHtml) {
        btnText = buttonHtml + buttonText;
      }

      $(`#${editorId} #selected-button`).append(
        `<button disabled class="${buttonOptions}">${btnText}</button>`
      );
    }

    function loadEvents() {
      const selection = editor.getSelection();
      const element = selection.getStartElement();
      const e = element.$;

      // If editor selection is a link...
      if (e.tagName === "A") {
        // Set button text to the link text
        buttonText = e.innerText.trim();
        const re = new RegExp(buttonText, "im");
        buttonHtml = e.innerHTML.replace(re, "");
        $(`#${editorId} #button-text`).val(buttonText);

        // Set link to the link href
        buttonLink = e.href;

        buttonLinkTarget = e.target || "_blank";
      }

      // Button Text
      const buttonTextInput = $(`#${editorId} #button-text`);
      buttonTextInput.keyup(() => {
        buttonText = buttonTextInput.val();
        preview();
      });

      // Button Color
      const colors = $(`#${editorId} .osu-colors p`);
      colors.click(function(e) {
        buttonColor =
          e.currentTarget.className === "default"
            ? "btn"
            : `osu-btn-${e.currentTarget.className}`;
        $(colors).removeClass("color-active");
        $(this).addClass("color-active");
        preview();
      });

      // Button Size
      const buttonSizes = $(`#${editorId} .button-sizes a`);
      buttonSizes.click(function(e) {
        buttonSize = e.currentTarget.id || "";
        $(buttonSizes).removeClass("size-active");
        $(this).addClass("size-active");
        preview();
      });

      preview();
    }

    const form = `
    <form id="${editorId}" class="osu-buttons">
      <div id="selected-button"></div>
      
      <div class="button-text">
        <label>Enter button text</label><br/>
        <input id="button-text" type="text" placeholder="Button Text">
      </div>
      
      <div class="osu-colors">
        <label>Choose a color</label>
        <p class="primary"></p>
        <p class="light"></p>
        <p class="dark"></p>
        <p class="white"></p>
        <p class="pine"></p>
        <p class="luminance"></p>
        <p class="rogue-wave"></p>
        <p class="stratosphere"></p>
      </div>
      
      <div class="button-sizes">
        <label>Choose a size</label><br/>
        <a id="btn-sm" class="btn btn-sm">Small</a>
        <a class="btn">Default</a>
        <a id="btn-lg" class="btn btn-lg">Large</a>
      </div>
    </form>
    `;

    return {
      title: "OSU Button Picker",
      midWidth: 600,
      minHeight: 300,
      contents: [
        {
          id: "tab-basic",
          label: "Basic Settings",
          elements: [
            {
              type: "html",
              id: "osu_button_tabs",
              html: form,
              onLoad: loadEvents
            }
          ]
        }
      ],
      onShow() {
        this.insertMode = true;
        loadEvents();
      },
      onOk() {
        const selection = editor.getSelection();
        const element = selection.getStartElement();
        const e = element.$;

        let buttonOptions = "btn";

        if (buttonSize) {
          buttonOptions += ` ${buttonSize}`;
        }

        if (buttonColor) {
          buttonOptions += ` ${buttonColor}`;
        }

        if (buttonHtml) {
          buttonText = buttonHtml + buttonText;
        }

        let button = "";
        if (buttonLink) {
          button = `<a class="${buttonOptions}" href="${buttonLink}" target="${buttonLinkTarget}">${buttonText}</a>`;
          e.remove();
        } else {
          button = `<a class="${buttonOptions}" href="" target="">${buttonText}</a>`;
        }

        buttonText = "Button Text";
        buttonSize = "";
        buttonColor = "osu-btn-primary";
        buttonLink = "";
        buttonHtml = "";

        if (this.insertMode) {
          // Insert button
          editor.insertHtml(button, "unfiltered_html");
        }
      }
    };
  });
})(jQuery);
