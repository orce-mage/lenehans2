/* global _ */
(function () {
    'use strict';

    var msgTmpl = _.template(
        '<div class="message <%- type %>">' +
            '<div><%- text %></div>' +
        '</div>'
    );

    window.soldTogetherValidator = {
        /**
         * @param  {jQuery}  $options
         * @return {Boolean}
         */
        isValidOptions: function ($options) {
            var isValid = true;

            $options.find('.swatch-input, .super-attribute').each(function () {
                if (!$(this).val()) {
                    isValid = false;

                    return false;
                }
            });

            return isValid;
        },

        /**
         * @param  {Object} message
         * @param  {jQuery} $container
         */
        showMessage: function (message, $container) {
            $container.append(msgTmpl(message));
        }
    };
})();
