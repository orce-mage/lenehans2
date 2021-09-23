define(["jquery", "prototype"], function ($) {
    'use strict';
    var Mb_Chooser = Class.create();
    Mb_Chooser.prototype = {
        initialize: function (url) {
            this.url = url;
        },
        showChooserElement: function (container) {
            var params = {};
            params.url = this.url;
            if (container && params.url) {
                var container2 = $('#'+container);
                params.data = {
                    id: container,
                    selected: container2.parents('div.admin__field').find('input[type="text"].entities').val()
                };
                this.showChooser(params, container);
            }
        },
        showChooser: function (params, container) {
            var container2 = $('#'+container);
            if (params.url && container) {
                if (container2.html() == '') {
                    new Ajax.Request(params.url, {
                        method: 'post',
                        parameters: params.data,
                        onSuccess: function (transport) {
                            try {
                                if (transport.responseText) {
                                    Element.insert(container, transport.responseText);
                                    container2.removeClass('no-display').show();
                                }
                            } catch (e) {
                                alert('Error occurs during loading chooser.');
                            }
                        }
                    });
                } else {
                    container2.removeClass('no-display').show();
                }
            }
        },
        hideChooserElement: function (container) {
            if ($('#'+container)) {
                $('#'+container).addClass('no-display').hide();
            }
        },
        checkCategory: function (event) {
            var node = event.memo.node;
            var container = event.target.up('div.admin__field');
            var elm = container.down('input[type="text"].entities');
            this.updateValue(node.id, elm, node.attributes.checked);
        },
        checkProduct: function (event) {
            var input = event.memo.element,
                container = event.target.up('div.admin__field'),
                elm = container.down('input[type="text"].entities');
            if (!isNaN(input.value)) this.updateValue(input.value, elm, input.checked);
        },
        updateValue: function (value, elm, isAdd) {
            var values = $(elm).val().strip();
            if (values) values = values.split(',');
            else values = [];
            if (isAdd) {
                if (-1 === values.indexOf(value)) {
                    values.push(value);
                    $(elm).val(values.join(','));
                }
            } else {
                if (-1 != values.indexOf(value)) {
                    values.splice(values.indexOf(value), 1);
                    $(elm).val(values.join(','));
                }
            }
        },
        cleanChooser: function (container) {
            var elm = $('#'+container).parents('div.admin__field').find('input[type="text"].entities');
            if (elm) elm.val('');
            var hidden = $('#'+container).find('input[type="hidden"]');
            if (hidden.length) hidden.val('');
            $('#'+container+' input[type="checkbox"]').prop("checked", false);
        }
    };
    return Mb_Chooser;
});