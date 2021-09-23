define(['underscore', 'uiRegistry', 'Magento_Ui/js/form/element/select', 'jquery', 'domReady!'], function(_, uiRegistry, select, $) {
    'use strict';
    return select.extend({
        /**
         * Init
         */
        initialize: function() {
            this._super();
            var value = this.value();
            this.fieldDepend(value, 'smartmenu_cat_column');
            this.fieldDepend(value, 'smartmenu_static_top');
            this.fieldDepend(value, 'smartmenu_static_bottom');
            this.fieldDepend(value, 'smartmenu_block_left');
            this.fieldDepend(value, 'smartmenu_static_left');
            this.fieldDepend(value, 'smartmenu_block_right');
            this.fieldDepend(value, 'smartmenu_static_right');
            return this;
        },
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function(value) {
            this.fieldDepend(value, 'smartmenu_cat_column');
            this.fieldDepend(value, 'smartmenu_static_top');
            this.fieldDepend(value, 'smartmenu_static_bottom');
            this.fieldDepend(value, 'smartmenu_block_left');
            this.fieldDepend(value, 'smartmenu_static_left');
            this.fieldDepend(value, 'smartmenu_block_right');
            this.fieldDepend(value, 'smartmenu_static_right');
            return this._super();
        },
        /**
         * Update field dependency
         *
         * @param {String} value
         */
        fieldDepend: function(value, field) {
            setTimeout(function () {
                var fieldIndex = uiRegistry.get('index = ' + field);
                if (fieldIndex.visibleValue != 'undefined') {
                    if (value == fieldIndex.visibleValue) {
                        fieldIndex.show();
                    } else {
                        fieldIndex.hide();
                    }
                }
            });
            return this;
        }
    });
});