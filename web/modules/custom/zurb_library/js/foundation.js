(function ($, Drupal) {
    Drupal.behaviors.zurb_foundation = {
        attach: function attach(context) {
            var need_init = true;
            const elems = $('[data-off-canvas]');
            elems.each(function (item) {
                if ($(elems[item]).data('zfPlugin')) {
                    need_init = false;
                }
            });


            if (need_init) {
                $(document).foundation();
            }

        }
    };
})(jQuery, Drupal);