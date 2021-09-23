/* global configForm */
define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';

    $.widget('swissup.successPagePreview', {
        options: {
            target: '',
            source: '',
            previewUrl: ''
        },

        /**
         * {@inheritdoc}
         */
        _create: function () {
            this.target = $(this.options.target);
            this.source = $(this.options.source);
            this._on({
                'click': this.saveAndPraview
            });
        },

        /**
         * Save config and start preview of Checkout Success Page
         */
        saveAndPraview: function () {
            if (configForm.validation('isValid')) {
                // save config
                $.ajax({
                    method: 'POST',
                    url: configForm.attr('action'),
                    data: configForm.serialize(),
                    context: this
                }).done(function (response) {
                    if (typeof response == 'object') {
                        this.handleJsonResponse(response);
                    } else {
                        this.handleHtmlResponse(response);
                    }

                    this.spinnerHide();
                });

                this.target.find('iframe').remove();
                this.spinnerShow();
            } else {
                // todo: config form is invalid
            }
        },

        /**
         * Show spinner over loading iframe
         */
        spinnerShow: function () {
            this.target.find('[data-role="iframe-placeholder"]')
                .show()
                .css('position', '');
        },

        /**
         * Hide spinner and scroll to iframe
         */
        spinnerHide: function () {
            $('html, body').animate({
                scrollTop: this.target.offset().top - 120
            }, 600);
            this.target.find('[data-role="iframe-placeholder"]')
                .css('position', 'absolute')
                .delay(6000)
                .hide(0); // pass 0 so delay could work on hide
        },

        /**
         * Get new hash and expire values from returned HTML
         *
         * @param  {HTML} html
         * @return {jQuery}
         */
        getNewHashAndExpire: function (html) {
            var regExp, matches;

            html = html.replace(/(\r\n|\n|\r)/gm, ''); // remove newlines
            regExp = new RegExp('<tr id="' + this.source.parent().attr('id') + '">(.*?)</tr>');
            matches = regExp.exec(html);

            if (!matches || typeof matches[0] == 'undefined') {
                return;
            }

            return $(matches[0]).find('.value input[type="hidden"]');
        },

        /**
         * Handle HTML response recived from server
         *
         * @param  {HTML} html
         */
        handleHtmlResponse: function (html) {
            // start preview on successful save
            var iframe;

            iframe = $('<iframe/>', {
                src: this.options.previewUrl.replace(
                        '{{orderNumber}}',
                        this.source.find('input[id$=order_to_preview]').val()
                    ).replace(
                        '{{previewHash}}',
                        $('#success_page_layout_preview_hash').val()
                    )
            }).css({
                'width': '100%',
                'border': '1px solid #d6d6d6',
                'min-height': '700px'
            });
            this.target.append(iframe);
            // get new values for preview hash and preview expire
            this.source.find('input[type="hidden"]').remove();
            this.source.append(this.getNewHashAndExpire(html));
        },

        /**
         * Handle JSON response recived from server
         *
         * @param  {JSON} json
         */
        handleJsonResponse: function (json) {
            if (json.error) {
                alert({
                    title: 'Error', content: json.message
                });
            } else if (json.ajaxExpired) {
                window.location.href = json.ajaxRedirect;
            }
        }
    });

    return $.swissup.successPagePreview;
});
