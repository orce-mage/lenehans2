/**
 * Pickup Options Comment UI Element
 */

define([
    'underscore',
    'ko',
    'Magento_Ui/js/form/element/textarea',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver'
], function (
    _,
    ko,
    Textarea,
    pickupDataResolver
) {
    'use strict';

    return Textarea.extend({
        defaults: {
            template: 'ui/form/field',
            inputName: 'ampickup_curbside_comment',
            valueUpdate: 'keyup',
            visible: true,
            cols: 30,
            rows: 4,
            required: false,
            validation: {
                'required-entry': false
            },
            imports: {
                isCart: '${$.parentName}:isCart',
                curbsideConfig: '${$.parentName}:curbsideConfig',
                curbsideChecked: '${$.parentName}:curbsideChecked'
            },
            listens: {
                '${$.parentName}:curbsideChecked': 'curbsideChecked',
                '${$.parentName}:visible': 'setVisibilityByParent'
            }
        },

        initialize: function () {
            var commentRequired;

            this._super();

            if (this.curbsideConfig) {
                this.placeholder = this.curbsideConfig.comment_placeholder;
            }

            commentRequired = !!(this.curbsideConfig && this.curbsideConfig.comment_field_required);

            this.visible(this.getCommentVisibility());
            this.required(commentRequired);
            this.validation['required-entry'] = commentRequired;
            this.value.subscribe(_.throttle(this.onCommentChange.bind(this), 500));

            return this;
        },

        initConfig: function () {
            var curbsideData;

            this._super();

            curbsideData = pickupDataResolver.curbsideData();

            this.value = curbsideData && Object.keys(curbsideData).length
                ? pickupDataResolver.curbsideData().comment
                : '';

            return this;
        },

        initObservable: function () {
            this._super()
                .observe([
                    'value',
                    'visible',
                    'curbsideChecked'
                ]);

            return this;
        },

        getCommentVisibility: function () {
            if (!this.curbsideConfig || this.isCart) {
                return false;
            }

            if (!this.curbsideConfig.checkbox_enabled) {
                return this.curbsideConfig.comments_enabled;
            }

            if (this.curbsideConfig.comments_enabled) {
                this.curbsideChecked.subscribe(this.visible);
            }

            return this.curbsideChecked()
                && this.curbsideConfig.comments_enabled;
        },

        onCommentChange: function () {
            pickupDataResolver.extendCurbsideData('comment', this.value());
        },

        /**
         * Set element visible same as parent component visible
         * @param {Boolean} parentVisibility
         * @returns {void}
         */
        setVisibilityByParent: function (parentVisibility) {
            this.visible(this.getCommentVisibility() && parentVisibility);
        }
    });
});
