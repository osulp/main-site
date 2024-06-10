(function ($) {
  Drupal.behaviors.addCustomBlocksBodyClass = {
    attach: function (context, settings) {
      let classesToCheck = [
        '.block--type-call-out-grid',
        '.block--type-content-with-image',
        '.block--type-cta-list-with-image',
        '.block--type-hero-with-cta',
        '.block--type-student-story'
      ];

      let foundClass = false;

      for (let i = 0; i < classesToCheck.length; i++) {
        if ($(classesToCheck[i], context).length > 0) {
          foundClass = true;
          break;
        }
      }

      if (foundClass) {
        $('body', context).addClass('osu-custom-blocks');
      }
    }
  };
})(jQuery);
