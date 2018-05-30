/**
 * @file
 * Javascript functionality for the ESES Theme.
 */

(function($, Drupal) {
  'use strict';

  Drupal.behaviors.eses_ui = {
    attach: function (context, settings) {
      $(".region-sidebar-first section", context).each(function () {
        $(this).on('click', function (event) {
          $(this).toggleClass('open').siblings().removeClass('open');
          event.stopPropagation();
        });
      });

      $(document, context).on('click', function () {
        $(".region-sidebar-first section").removeClass('open');
      });

    }
  }

})(jQuery, Drupal);