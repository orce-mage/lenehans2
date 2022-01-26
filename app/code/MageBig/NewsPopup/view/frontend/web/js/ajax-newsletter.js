/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'magnificpopup'
], function ($, customerData) {
    'use strict';

    return function (config, element, callback) {
        $(element).on('submit', function (event) {
            var $form = $(event.currentTarget);
            event.preventDefault();
            if ($form.validation() && $form.validation('isValid')) {
                var formUrl = $form.attr('action');

                $.ajax({
                    url: formUrl,
                    data: $form.serialize(),
                    type: 'post',
                    beforeSend: function () {
                        $('body').trigger('processStart');
                    },
                    success: function (res) {
                        if (res.success) {
                            if (typeof callback === 'function') {
                                callback(config.cookieFlag, config.cookieLifetime, config.showhome);
                            }

                            if ($.magnificPopup.instance.isOpen) {
                                $.magnificPopup.close();
                            }

                            customerData.set('messages', {
                                messages: [{
                                    type: 'success',
                                    text: res.message
                                }],
                                'data_id': Math.floor(Date.now() / 1000)
                            });
                        } else {
                            customerData.set('messages', {
                                messages: [{
                                    type: 'error',
                                    text: res.message
                                }],
                                'data_id': Math.floor(Date.now() / 1000)
                            });
                        }
                    },
                    error: function () {
                        console.log('subscribe error');
                    }
                }).done(function (res) {
                    $('body').trigger('processStop');

                    if (typeof grecaptcha !== 'undefined') {
                        var c = $('.g-recaptcha').length;
                        for (var i = 0; i < c; i++)
                            grecaptcha.reset(i);

                        $('input[name="token"]').val('');
                    }
                });
            }
        });
    };
});
