/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'magnificpopup'
], function ($, customerData, $t) {
    'use strict';

    $.widget('magebig.socialpopup', {
        options: {
            /*General*/
            popup: '#social-login-popup',
            popupEffect: '',
            headerLink: '.header .links, .section-item-content .header.links',
            ajaxLoading: '#social-login-popup .ajax-loading',
            loadingClass: 'social-login-ajax-loading',
            errorMsgClass: 'message-error error message',
            successMsgClass: 'message-success success message',
            /*Login*/
            loginFormContainer: '.social-login.authentication',
            loginFormContent: '.social-login.authentication .social-login-customer-authentication .block-content',
            loginForm: '#social-form-login',
            loginBtn: '#btn-social-login-authentication',
            forgotBtn: '#social-form-login .action.remind',
            createBtn: '.social-login.authentication .action.create',
            formLoginUrl: '',
            /*Email*/
            emailFormContainer: '.social-login.fake-email',
            fakeEmailSendBtn: '#social-form-fake-email .action.send',
            fakeEmailType: '',
            fakeEmailFrom: '#social-form-fake-email',
            fakeEmailFormContent: '.social-login.fake-email .block-content',
            fakeEmailUrl: '',
            fakeEmailCancelBtn: '#social-form-fake-email .action.cancel',
            /*Forgot*/
            forgotFormContainer: '.social-login.forgot',
            forgotFormContent: '.social-login.forgot .block-content',
            forgotForm: '#social-form-password-forget',
            forgotSendBtn: '#social-form-password-forget .action.send',
            forgotBackBtn: '.social-login.forgot .action.back',
            forgotFormUrl: '',
            /*Create*/
            createFormContainer: '.social-login.create',
            createFormContent: '.social-login.create .block-content',
            createForm: '#social-form-create',
            createAccBtn: '#social-form-create .action.create',
            createBackBtn: '.social-login.create .action.back',
            createFormUrl: '',
            popupCreate: 0,
            popupForgot: 0
        },

        /**
         * @private
         */
        _create: function () {
            var self = this;
            this.initObject();
            this.initLink();
            this.initObserve();
            window.fakeEmailCallback = function (type) {
                self.options.fakeEmailType = type;
                self.showEmail();
            };
        },

        /**
         * Init object will be used
         */
        initObject: function () {
            this.loginForm = $(this.options.loginForm);
            this.createForm = $(this.options.createForm);
            this.forgotForm = $(this.options.forgotForm);

            this.forgotFormContainer = $(this.options.forgotFormContainer);
            this.createFormContainer = $(this.options.createFormContainer);
            this.loginFormContainer = $(this.options.loginFormContainer);

            this.loginFormContent = $(this.options.loginFormContent);
            this.forgotFormContent = $(this.options.forgotFormContent);
            this.createFormContent = $(this.options.createFormContent);

            this.emailFormContainer = $(this.options.emailFormContainer);
            this.fakeEmailFrom = $(this.options.fakeEmailFrom);
            this.fakeEmailFormContent = $(this.options.fakeEmailFormContent);
        },

        /**
         * Init links login
         */
        initLink: function () {
            var self = this,
                headerLink = $(this.options.headerLink);

            if (headerLink.length) {
                headerLink.find('a').each(function (link) {
                    var el = $(this),
                        href = el.attr('href');

                    if (typeof href !== 'undefined' && href.search('customer/account/login') !== -1) {
                        el.addClass('social-login');
                        el.attr('href', self.options.popup);
                        el.attr('data-effect', self.options.popupEffect);
                        el.on('click', function (event) {
                            event.preventDefault();
                            self.showLogin();
                        });
                        $('.wishlist-topbar').on('click', '.wishlist-icon', function (event) {
                            event.preventDefault();
                            el.trigger('click');
                        });
                    }
                    if ((typeof href !== 'undefined' && href.search('customer/account/create') !== -1 && self.options.popupCreate) == 1) {
                        el.addClass('social-login');
                        el.attr('href', self.options.popup);
                        el.attr('data-effect', self.options.popupEffect);
                        el.on('click', function (event) {
                            event.preventDefault();
                            self.showCreate();
                        });
                    }
                });

                self.initPopup(headerLink);
            }

            this.options.createFormUrl = this.correctUrlProtocol(this.options.createFormUrl);
            this.options.formLoginUrl = this.correctUrlProtocol(this.options.formLoginUrl);
            this.options.forgotFormUrl = this.correctUrlProtocol(this.options.forgotFormUrl);
            this.options.fakeEmailUrl = this.correctUrlProtocol(this.options.fakeEmailUrl);
        },

        initPopup: function (elm) {
            elm.magnificPopup({
                delegate: '.social-login',
                removalDelay: 300,
                mainClass: 'mfp-move-from-top',
                closeOnBgClick: false,
                callbacks: {
                    // beforeOpen: function () {
                    //     this.st.mainClass = this.st.el.attr('data-effect');
                    // },
                    open: function() {
                        if( this.fixedContentPos ) {
                            if(this._hasScrollBar(this.wH)){
                                var s = this._getScrollbarSize();
                                if(s) {
                                    $('.sticky-menu.active').css('padding-right', s);
                                    $('#go-top').css('margin-right', s);
                                }
                            }
                        }
                    },
                    close: function() {
                        $('.sticky-menu.active').css('padding-right', '');
                        $('#go-top').css('margin-right', '');
                    }
                },
                midClick: true
            });
        },

        /**
         * Correct url protocol to match with current protocol
         * @param url
         * @returns {*}
         */
        correctUrlProtocol: function (url) {
            var protocol = window.location.protocol;

            if (url.indexOf(protocol) === -1) {
                url = url.replace(/http:|https:/gi, protocol);
            }

            return url;
        },

        /**
         * Init button click
         */
        initObserve: function () {
            var self = this,
                submitBtnLogin = $(this.options.loginBtn),
                submitBtnCreate = $(this.options.createAccBtn),
                submitBtnForgot = $(this.options.forgotSendBtn);

            submitBtnLogin.on('click', function () {
                self.initLoginObserve();
            });

            this.initEmailObserve();

            if (this.options.popupCreate) {
                submitBtnCreate.on('click', function () {
                    self.initCreateObserve();
                });

                $(this.options.createBtn).on('click', function (event) {
                    event.preventDefault();
                    self.showCreate();
                });
                $(this.options.createBackBtn).on('click', self.showLogin.bind(this));
            }

            if (this.options.popupForgot) {
                submitBtnForgot.on('click', function () {
                    self.initForgotObserve();
                });
                $(this.options.forgotBtn).on('click', function (event) {
                    event.preventDefault();
                    self.showForgot();
                });
                $(this.options.forgotBackBtn).on('click', self.showLogin.bind(this));
            }

            this.loginForm.find('input').keypress(function (event) {
                var code = event.keyCode;
                if (code === 13) {
                    self.initLoginObserve();
                }
            });

            this.createForm.find('input').keypress(function (event) {
                var code = event.keyCode || event.which;
                if (code === 13) {
                    self.initCreateObserve();
                }
            });

            this.forgotForm.find('input').keypress(function (event) {
                var code = event.keyCode || event.which;
                if (code === 13) {
                    self.initForgotObserve();
                }
            });

            this.fakeEmailFrom.find('input').keypress(function (event) {
                var code = event.keyCode || event.which;
                if (code === 13) {
                    self.processEmail();
                }
            });

        },

        /**
         * Login process
         */
        initLoginObserve: function () {
            var self = this,
                submitBtn = $(this.options.loginBtn),
                form = this.loginForm;

            form.submit(function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                self.processLogin();
            });
        },

        /**
         * Create process
         */
        initCreateObserve: function () {
            var self = this,
                submitBtn = $(this.options.createAccBtn),
                form = this.createForm;

            form.submit(function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                self.processCreate();
            });
        },

        /**
         * Forgot process
         */
        initForgotObserve: function () {
            var self = this,
                submitBtn = $(this.options.forgotSendBtn),
                form = this.forgotForm;

            form.submit(function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                self.processForgot();
            });
        },

        /**
         * Email process
         */
        initEmailObserve: function () {
            var self = this;

            $(this.options.fakeEmailSendBtn).on('click', this.processEmail.bind(this));
        },

        /**
         * Show Login page
         */
        showLogin: function () {
            //this.reloadCaptcha('login', 500);
            this.loginFormContainer.show();
            this.forgotFormContainer.hide();
            this.createFormContainer.hide();
            this.emailFormContainer.hide();
        },

        /**
         * Show email page
         */
        showEmail: function () {
            this.loginFormContainer.hide();
            this.forgotFormContainer.hide();
            this.createFormContainer.hide();
            this.emailFormContainer.show();
        },

        /**
         * Show create page
         */
        showCreate: function () {
            //this.reloadCaptcha('create', 500);
            this.loginFormContainer.hide();
            this.forgotFormContainer.hide();
            this.createFormContainer.show();
            this.emailFormContainer.hide();
        },

        /**
         * Show forgot password page
         */
        showForgot: function () {
            //this.reloadCaptcha('forgot', 500);
            this.loginFormContainer.hide();
            this.forgotFormContainer.show();
            this.createFormContainer.hide();
            this.emailFormContainer.hide();
        },

        /**
         * Reload captcha if enabled
         * @param type
         * @param delay
         */
        reloadCaptcha: function (type, delay) {
            if (typeof this.captchaReload === 'undefined') {
                this.captchaReload = {
                    all: $('#social-login-popup .captcha-reload'),
                    login: $('#social-login-popup .authentication .captcha-reload'),
                    create: $('#social-login-popup .create .captcha-reload'),
                    forgot: $('#social-login-popup .forgot .captcha-reload')
                };
            }

            if (typeof type === 'undefined') {
                type = 'all';
            }

            if (this.captchaReload.hasOwnProperty(type) && this.captchaReload[type].length) {
                if (typeof delay === 'undefined') {
                    this.captchaReload[type].trigger('click');
                } else {
                    var self = this;
                    setTimeout(function () {
                        self.captchaReload[type].trigger('click');
                    }, delay);
                }
            }
        },

        /**
         * Process login
         */
        processLogin: function () {
            if (!this.loginForm.valid()) {
                return;
            }

            var self = this,
                options = this.options,
                loginData = {},
                formDataArray = this.loginForm.serializeArray();

            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
                if (entry.name.includes('user_login')) {
                    loginData['captcha_string'] = entry.value;
                    loginData['captcha_form_id'] = 'user_login';
                }
            });

            this.appendLoading(this.loginFormContent);
            this.removeMsg(this.loginFormContent, options.errorMsgClass);

            return $.ajax({
                url: options.formLoginUrl,
                type: 'POST',
                data: JSON.stringify(loginData)
            }).done(function (response) {
                response.success = !response.errors;
                self.addMsg(self.loginFormContent, response);
                if (response.success) {
                    customerData.invalidate(['customer']);
                    if (response.redirectUrl) {
                        window.location.href = response.redirectUrl;
                    } else {
                        window.location.reload();
                    }
                } else {
                    // self.reloadCaptcha('login');
                    self.removeLoading(self.loginFormContent);
                    self.reloadRecaptcha();
                }
            }).fail(function () {
                // self.reloadCaptcha('login');
                self.addMsg(self.loginFormContent, {
                    message: $t('Could not authenticate. Please try again later'),
                    success: false
                });
                self.removeLoading(self.loginFormContent);
                self.reloadRecaptcha();
            });
        },

        /**
         * Process forgot
         */
        processForgot: function () {
            if (!this.forgotForm.valid()) {
                return;
            }

            var self = this,
                options = this.options,
                parameters = this.forgotForm.serialize();

            this.appendLoading(this.forgotFormContent);
            this.removeMsg(this.forgotFormContent, options.errorMsgClass);
            this.removeMsg(this.forgotFormContent, options.successMsgClass);

            return $.ajax({
                url: options.forgotFormUrl,
                type: 'POST',
                data: parameters
            }).done(function (response) {
                // self.reloadCaptcha('forgot');
                self.addMsg(self.forgotFormContent, response);
                self.removeLoading(self.forgotFormContent);
                self.reloadRecaptcha();
            });
        },

        /**
         * Process email
         */
        processEmail: function () {
            if (!this.fakeEmailFrom.valid()) {
                return;
            }
            var input = $("<input>")
                .attr("type", "hidden")
                .attr("name", "type").val(this.options.fakeEmailType.toLowerCase());
            $(this.fakeEmailFrom).append($(input));

            var self = this;
            var options = this.options,
                parameters = this.fakeEmailFrom.serialize();

            this.appendLoading(this.fakeEmailFormContent);
            this.removeMsg(this.fakeEmailFormContent, options.errorMsgClass);
            this.removeMsg(this.fakeEmailFormContent, options.successMsgClass);

            return $.ajax({
                url: options.fakeEmailUrl,
                type: 'POST',
                data: parameters
            }).done(function (response) {
                self.addMsg(self.fakeEmailFrom, response);
                self.removeLoading(self.fakeEmailFormContent);
                if (response.success) {
                    if (response.url == '' || response.url == null) {
                        location.reload();
                    } else {
                        window.location.href = response.url;
                    }
                }
            });
        },

        /**
         * Process create account
         */
        processCreate: function () {
            if (!this.createForm.valid()) {
                return;
            }

            var self = this,
                options = this.options,
                parameters = this.createForm.serialize();

            this.appendLoading(this.createFormContent);
            this.removeMsg(this.createFormContent, options.errorMsgClass);

            return $.ajax({
                url: options.createFormUrl,
                type: 'POST',
                data: parameters
            }).done(function (response) {
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else if (response.success) {
                    customerData.invalidate(['customer']);
                    self.addMsg(self.createFormContent, response);
                    location.reload();
                } else {
                    // self.reloadCaptcha('create');
                    self.addMsg(self.createFormContent, response);
                    self.removeLoading(self.createFormContent);
                    self.reloadRecaptcha();
                }
            });
        },

        /**
         * @param block
         */
        appendLoading: function (block) {
            block.css('position', 'relative');
            block.prepend($("<div></div>", {"class": this.options.loadingClass}))
        },

        /**
         * @param block
         */
        removeLoading: function (block) {
            block.css('position', '');
            block.find("." + this.options.loadingClass).remove();
        },

        /**
         * @param block
         * @param response
         */
        addMsg: function (block, response) {
            var message = response.message,
                messageClass = response.success ? this.options.successMsgClass : this.options.errorMsgClass;

            if (typeof(message) === 'object' && message.length > 0) {
                message.forEach(function (msg) {
                    this._appendMessage(block, msg, messageClass);
                }.bind(this));
            } else if (typeof(message) === 'string') {
                this._appendMessage(block, message, messageClass);
            }
        },

        /**
         * @param block
         * @param messageClass
         */
        removeMsg: function (block, messageClass) {
            block.find('.' + messageClass.replace(/ /g, '.')).remove();
        },

        /**
         * @param block
         * @param message
         * @param messageClass
         * @private
         */
        _appendMessage: function (block, message, messageClass) {
            var currentMessage = null;
            var messageSection = block.find("." + messageClass.replace(/ /g, '.'));
            if (!messageSection.length) {
                block.prepend($('<div></div>', {'class': messageClass}));
                currentMessage = block.children().first();
            } else {
                currentMessage = messageSection.first();
            }

            currentMessage.append($('<div>' + message + '</div>'));
        },

        reloadRecaptcha: function () {
            if (typeof grecaptcha !== 'undefined') {
                var c = $('.g-recaptcha').length;
                if (c) {
                    for (var i = 0; i < c; i++)
                        grecaptcha.reset(i);

                    $('input[name="token"]').val('');
                }
            }
        }
    });

    return $.magebig.socialpopup;
});
