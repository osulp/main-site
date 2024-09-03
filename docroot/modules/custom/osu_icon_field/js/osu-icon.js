import {
  osuIconNames,
  faIconNames,
  iconSizes,
  faBrandIconsNames,
} from './icon-list';

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.iconPicker = {
    attach: function (context, settings) {
      $(once('body', context)).each(function (i, item) {
        addAutoComplete();
      });
    },
  };

  /**
   * Add autocomplete suggestions to icon input field
   */
  function addAutoComplete() {
    // built in autocomplete interferes with icon autocomplete
    $('#osuIconInput').attr('autocomplete', 'off');

    $(document).on('keyup', '#osuIconInput', function () {
      // remove old autocomplete
      closeAutoComplete();
      // remove autocomplete on focus loss
      $('#osuIconInput').focusout((e) => {
        if (e.relatedTarget) {
          const target = $(e.relatedTarget).parent().parent().parent()[0];
          const children = [...$('#osuIconInput').parent()[0].children];
          if (!children.includes(target)) {
            closeAutoComplete();
          }
        } else {
          closeAutoComplete();
        }
      });

      // construct autocomplete items
      let autocompleteItems = `
        <div class="autocomplete-wrapper">
          <ul class="autocomplete-items">
      `;

      // grab search term and find matches
      const searchTerm = $(this).val();
      const osuMatches = findMatches(osuIconNames, 'icon-osu', searchTerm);
      autocompleteItems += osuMatches;
      const faMatches = findMatches(faIconNames, 'fa', searchTerm);
      autocompleteItems += faMatches;
      const faBrandMatches = findMatches(faBrandIconsNames, 'fab', searchTerm);
      autocompleteItems += faBrandMatches;

      autocompleteItems += `
          </ul>
        </div>
      `;
      if (osuMatches || faMatches || faBrandMatches) {
        $(this).after(autocompleteItems);
      }

      // remove old click events to prevent multiple actions at once
      $(document).off('click', '.autocomplete-wrapper a.osu-icon');
      $(document).on('click', '.autocomplete-wrapper a.osu-icon', function (e) {
        e.preventDefault();

        const iconChoice = $(this).text();
        const osu_icon_input = $(this).parent().parent().parent().parent().find('#osuIconInput');
        $(osu_icon_input).val(iconChoice);

        closeAutoComplete();
      });

      // hover css
      $(document).off('mouseover', '.autocomplete-wrapper a.osu-icon');
      $(document).on('mouseover', '.autocomplete-wrapper a.osu-icon', function (e) {
        $(this).addClass('autocomplete-hover');
      });
      $(document).off('mouseout', '.autocomplete-wrapper a.osu-icon');
      $(document).on('mouseout', '.autocomplete-wrapper a.osu-icon', function (e) {
        $(this).removeClass('autocomplete-hover');
      });
    });

    /**
     * Removes autocomplete div from page
     */
    function closeAutoComplete() {
      $('.autocomplete-wrapper').remove();
    }

    /**
     * Finds and returns a list of icon matches based on the provided icon names, icon type, and search term.
     *
     * @param {string[]} iconNames - The list of icon names without the full class name.
     * @param {string} iconType - The type of icon (osu, fa, fab).
     * @param {string} searchTerm - The user input used to match icons with.
     *
     * @returns {string} - A string containing HTML list items (<li>) with anchor tags (<a>) representing matched icons.
     */
    function findMatches(iconNames, iconType, searchTerm) {
      let autocompleteItems = '';
      if (searchTerm !== '') {
        let matches = 0;
        const MAX_MATCHES = 10;
        iconNames.forEach(name => {
          // catch invalid regex errors from user input
          try {
            const match = name.search(searchTerm);
            if (match > -1 && matches <= MAX_MATCHES) {
              let computedIconName = computeIconName(iconType);
              autocompleteItems += `<li><a class="osu-icon" href="#"><i class="${computedIconName}-${name}"></i>${computedIconName}-${name}</a></li>`;
              matches++;
            }
          } catch (err) {
          }
        });
      }

      return autocompleteItems;
    }
  }

  /**
   * Computes the icon name based on the provided icon type.
   *
   * @param {string} iconType - The type of the icon ('fa' or 'fab').
   * @return {string} - The computed icon name.
   */
  function computeIconName(iconType) {
    if (iconType === 'fa') {
      return 'fas fa';
    } else if (iconType === 'fab') {
      return 'fab fa';
    } else {
      return 'icon-osu';
    }
  }

  /**
   * Adds size select dropdown below icon text field
   */
  function addSizeSelect() {
    const parent = $('#osuIconInput').parent();

    const selectListId = 'osu-icon-size-select';
    var sizeSelect = `
      <div>
        <label class="form-item__label">Icon Size</label>
        <select id="${selectListId}" class="form-select form-element form-element--type-select">
    `;

    iconSizes.forEach(size => {
      sizeSelect += `<option value="${size.class}">${size.label}</option>`;
    });

    sizeSelect += `
        </select>
      </div>
    `;
    $(parent).append(sizeSelect);

    // get selected icon size and add to text field
    $(`#${selectListId}`).change(() => {
      const selectedOption = $(`#${selectListId} option:selected`).val();
      const osu_icon_input = $('#osuIconInput');
      const currentInput = $(osu_icon_input).val().split(' ');
      if (currentInput.length >= 1) {
        currentInput[1] = selectedOption;
        $(osu_icon_input).val(currentInput.join(' '));
      } else {
        $(osu_icon_input).val(` ${selectedOption}`);
      }
    });

    // set select list to default if size text is removed
    $(parent).change(() => {
      if ($('#osuIconInput').val().split(' ').length < 2) {
        $(`#${selectListId}`).val('');
      }
    });
  }

  // TODO: use this to easily generate list of osu icons, probably ran on command line and committed
  // async function getOsuIconNames () {
  // var iconNames = [];
  // var response = await fetch(osuIconUrl, {
  // headers: {
  // 'Access-Control-Allow-Origin':'*'
  // },
  // });
  // var data = await response.text();

  // // parse css to get icon names
  // iconNames = data.match(/\.icon-osu-[a-zA-Z0-9_-]*:/g);
  // // trim css class names down to just the icon names
  // iconNames.forEach( (name, i) => {
  // iconNames[i] = name.replace('.icon-osu-', "").slice(0, -1);
  // });

  // return iconNames;
  // };
})(jQuery, Drupal, drupalSettings, once);
