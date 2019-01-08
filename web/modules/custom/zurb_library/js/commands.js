(function ($, Drupal) {
    Drupal.AjaxCommands.prototype.ZurbFoundationOffCanvas = function (ajax, response, status) {
        $(response.selector).foundation(response.action);
    }
})(jQuery, Drupal);