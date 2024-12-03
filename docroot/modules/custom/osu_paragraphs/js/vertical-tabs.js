/**
 * @file
 * Custom JavaScript for vertical tabs.
 * phpcs:ignoreFile
 */
(function ($, Drupal, once) {
  Drupal.behaviors.osuParagraphsVerticalTabs = {
    attach: function (context, settings) {
      once('osu-paragraphs-vertical-tabs', 'div.paragraph.tab-wrapper-paragraph', context).forEach((tabWrapperElement) => {
        /**
         * Handles click events for tab links within a tab wrapper.
         * Prevents the default action of the event, processes the tab click to show the
         * corresponding tab content, and updates the active states and ARIA attributes.
         *
         * @param {Event} event - The click event fired by the tab link.
         */
        const handleClick = (event) => {
          event.preventDefault();

          const $tabWrapper = $(tabWrapperElement);
          const $clickedTabLink = $(event.currentTarget);
          const targetSelector = $clickedTabLink.attr('href');
          const $targetContent = $(targetSelector);
          const $parentTabs = $clickedTabLink.closest('.tab-wrapper-paragraph__tabs');

          // Get the height of all the tabs.
          const $allTabHeight = $('.tab-wrapper-paragraph__tab')
            .toArray()
            .reduce((accumulator, tab) => Math.ceil($(tab).outerHeight(true)) + accumulator, 0);

          const $allActiveTabs = $tabWrapper.find('.tab-wrapper-paragraph__tab.active');
          const $allActiveTabLinks = $tabWrapper.find('.tab-wrapper-paragraph__tab-link--active');
          const $allActiveTabContents = $tabWrapper.find('.tab-wrapper-paragraph__tab-content--active');

          // Remove active classes and aria-hidden attributes
          $allActiveTabs.removeClass('active');
          $allActiveTabLinks.removeClass('tab-wrapper-paragraph__tab-link--active');
          $allActiveTabContents.removeClass('tab-wrapper-paragraph__tab-content--active').css('max-height', '').attr('aria-hidden', 'true');

          // Add active classes and remove aria-hidden attribute
          $clickedTabLink.parent().addClass('active');
          $clickedTabLink.addClass('tab-wrapper-paragraph__tab-link--active');
          $targetContent.addClass('tab-wrapper-paragraph__tab-content--active').removeAttr('aria-hidden');

          // Adjust heights
          $targetContent.css('max-height', $targetContent.prop('scrollHeight') + 'px');
          // Set the height of the osu-tabs container to the height of the controlledPanel
          // if window width is greater than 769 px.
          if (window.innerWidth > 768) {
            const $targetTabContentHeight = $targetContent.prop('scrollHeight');
            // Check to ensure
            // that the new height is not smaller than the total tab height.
            if ($allTabHeight > $targetTabContentHeight) {
              $parentTabs.css('height', $allTabHeight + 'px');
            } else {
              $parentTabs.css('height', $targetTabContentHeight + 'px');
            }
          }

        };

        const $tabLinks = $(tabWrapperElement).find('a.tab-wrapper-paragraph__tab-link');
        $tabLinks.on('click', handleClick);

      });
      /**
       * Handles changes in the URL hash and triggers click events based on the hash value.
       * Parses the hash and determines if it relates to a tab link or tab content,
       * then simulates a click event on the corresponding element.
       *
       * @return {void}
       */
      const handleHashChange = () => {
        const hash = window.location.hash;
        if (hash) {
          const $tabArr = hash.split('-');
          if ($tabArr[0] === '#tab') {
            if ($tabArr[1] === 'link') {
              $(`a[id="${hash.substring(1)}"]`).trigger('click');
            } else if ($tabArr[1] === 'content') {
              $(`a[href="${hash}"]`).trigger('click');
            }
          }
        }
      };
      if (window.innerWidth > 768) {
        // Find all first tabs and for each one click on it.
        once('osu-paragraphs-vertical-tabs-first-tab', 'div.paragraph.tab-wrapper-paragraph', context).forEach((tabWrapperElement) => {
          $(tabWrapperElement).find('a.tab-wrapper-paragraph__tab-link').first().trigger('click');
        });
      }
      // If an anchor link is used, open the linked tab item.
      handleHashChange();
      // If we load on the same page, run the script again.
      $(window).on('hashchange', handleHashChange);

    },
  };
})(jQuery, Drupal, once);
