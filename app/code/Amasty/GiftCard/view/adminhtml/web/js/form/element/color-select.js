define([
    'Magento_Ui/js/form/element/abstract',
    'mageUtils',
    'jquery',
    'jquery/colorpicker/js/colorpicker'
], function (Element, utils, $) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true,
            label: '',
            error: '',
            uid: utils.uniqueid(),
            disabled: false,
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },

        initialize: function () {
            this._super();
        },

        initColorPickerCallback: function (element) {
            var self = this;
            $(element).css("backgroundColor", '#' + element.value)

            $(element).ColorPicker({
                onSubmit: function(hsb, hex, rgb, el) {
                    self.value(hex);
                    $(el).ColorPickerHide();
                },
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                },
                onChange: function (hsb, hex, rgb) {
                    self.value('#' + hex);
                    $(element).css("backgroundColor", "#" + hex);
                }
            }).bind('keyup', function(){
                $(this).ColorPickerSetColor(this.value);
            });
        }
    });
});
