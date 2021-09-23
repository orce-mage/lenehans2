/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'socialProvider'
    ],
    function ($, ko, Component, socialProvider) {
        'use strict';

        /**
         * @type {{init: ko.bindingHandlers.socialButton.init}}
         */
        ko.bindingHandlers.socialButton = {
            init: function (element, valueAccessor, allBindings) {
                var config = {
                    url: allBindings.get('url'),
                    label: allBindings.get('label')
                };

                socialProvider(config, element);
            }
        };

        return Component.extend({
            defaults: {
                template: 'MageBig_SocialLogin/social-buttons'
            },
            buttonLists: window.socialAuthenticationPopup,

            /**
             * @returns {Array}
             */
            socials: function () {
                var socials = [];

                $.each(this.buttonLists, function (key, social) {
                    socials.push(social);
                });

                return socials;
            },

            /**
             * @returns {boolean}
             */
            isActive: function () {
                return (typeof this.buttonLists !== 'undefined');
            }
        });
    }
);
