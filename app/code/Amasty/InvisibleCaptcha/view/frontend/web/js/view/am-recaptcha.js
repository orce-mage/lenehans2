define([
    'jquery',
    'ko',
    'underscore',
    'mageUtils',
    'Amasty_InvisibleCaptcha/js/view/am-recaptcha-abstract',
    'Amasty_InvisibleCaptcha/js/model/am-recaptcha',
    'mage/loader',
    'domReady!'
], function (
    $,
    ko,
    _,
    utils,
    Component,
    amReCaptchaModel
) {
    'use strict';

    return Component.extend({
        defaults: {
            formsToProtect: '',
            captchaElementClass: 'am-recaptcha-block'
        },

        /**
         * @inheritDoc
         * @returns {Object}
         */
        initObservable: function () {
            this._super();

            this.formsToProtect = $(amReCaptchaModel.getFormsList());
            this.renderCaptcha();

            return this;
        },

        /**
         * @returns {void}
         */
        renderCaptcha: function () {
            $(window).on('amcaptchaReady', this.initFormHandler.bind(this));

            // eslint-disable-next-line consistent-return
            _.debounce(function () {
                this.formsToProtect.on('submit', function (event) {
                    var form = $(event.currentTarget);
                    if (amReCaptchaModel.isScriptLoaded) {
                        form.off('submit:beforeSubmit');

                        return;
                    }

                    event.preventDefault();
                    event.stopImmediatePropagation();

                    form.trigger('submit:beforeSubmit');

                }.bind(this));

                this._addListeners();
                this._eventOrderChange();
            }.bind(this), 200)();
        },

        _addListeners: function () {
            this.formsToProtect.on('submit:beforeSubmit', function (event) {
                if (amReCaptchaModel.isScriptLoaded) {
                    return;
                }

                this.cachedForm = $(event.target);
                this.loadApi();

                return false;
            }.bind(this));
        },

        /**
         * @private
         * @returns {void}
         */
        _eventOrderChange: function () {
            _.each(this.formsToProtect, function (form) {
                var $form = $(form);

                $form.data('recaptchaFormId', utils.uniqueid());

                if (+amReCaptchaModel.invisibleCaptchaCustomForm) {
                    this._swapSubmit($form);
                }
            }.bind(this));
        },

        /**
         * @param {Element} form
         * @private
         * @returns {void}
         */
        _swapSubmit: function (form) {
            var $form = $(form),
                listeners;

            listeners = $._data($form[0], 'events').submit;
            listeners.unshift(listeners.pop());
        },

        /**
         * @param {Element} form
         * @returns {Object}
         */
        getParameters: function (form) {
            var $form = $(form);

            return _.extend(amReCaptchaModel.getRecaptchaConfig(), {
                'callback': function () {
                    if (this.showLoaderOnCaptchaLoading) {
                        $('body').trigger('processStop');
                    }

                    if ($form.valid()) {
                        $form.submit();
                    }
                }.bind(this),
                'expired-callback': this.resetCaptcha
            });
        },

        /**
         * @returns {void}
         */
        initFormHandler: function () {
            amReCaptchaModel.isScriptLoaded = true;
            this.appendCaptcha();
            _.each(this.formsToProtect, function (form) {
                var $form = $(form),
                    widgetId = this._initCaptchaOnForm(form);

                $form.on('ajaxFormLoaded', function () {
                    this._formButtonClickEvent(form, widgetId);
                }.bind(this));

            }.bind(this));
        },

        /**
         * Init captcha on form
         * @param {Element} form
         * @returns {String|Number}
         */
        _initCaptchaOnForm: function (form) {
            var $form = $(form),
                widgetId,
                $button = $form.find("[type='submit']"),
                $captchaElement = $('<div class="' + this.captchaElementClass + '"></div>');

            $form.append($captchaElement);

            widgetId = grecaptcha.render($captchaElement[0], this.getParameters($form));

            $captchaElement.data('id', widgetId);

            this._formButtonClickEvent(form, widgetId);
            this._submitCachedForm($form, $button);

            amReCaptchaModel.tokenFields.push($captchaElement);

            return widgetId;
        },

        /**
         *
         * @param {Element} $form
         * @param {Element} $button
         * @private
         * @returns {void}
         */
        _submitCachedForm: function ($form, $button) {
            if ($button.length && this.cachedForm
                && this.cachedForm.data('recaptchaFormId') === $form.data('recaptchaFormId')) {
                if (this.showLoaderOnCaptchaLoading) {
                    $('body').trigger('processStart');
                }

                $button.trigger('click');
                this.cachedForm = null;
            }
        },

        /**
         * Add Event to execute recaptcha widget on button click
         * @private
         * @param {Element} form
         * @param {Number} widgetId
         * @returns {void}
         */
        _formButtonClickEvent: function (form, widgetId) {
            var $form = $(form),
                $button = $form.find("[type='submit']");

            $button.on('click', function (e) {
                e.preventDefault();

                if ($form.valid()) {
                    grecaptcha.reset(widgetId);
                    grecaptcha.execute(widgetId);
                }
            });
        }
    });
});
