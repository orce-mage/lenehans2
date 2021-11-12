define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    $.widget('mst.finderFilter', {
        $select: null,

        options: {
            filter_id:   null,
            url_key:     '',
            is_required: true
        },

        _create: function () {
            this.$select = $('[data-select]', this.element);

            $('[data-select]', this.element).on('change', function () {
                this._trigger('_change');
            }.bind(this))
        },

        filterId: function () {
            return this.options.filter_id;
        },

        urlKey: function () {
            return this.options.url_key;
        },

        isRequired: function () {
            return this.options.is_required;
        },

        setIsDisabled: function (value) {
            if (value) {
                $(this.element).attr('disabled', value);
                this.$select.attr('disabled', value);
            } else {
                $(this.element).removeAttr('disabled')
                this.$select.removeAttr('disabled');
            }
            
            if (typeof $('select', this.element).chosen != 'undefined') {
                $('select', this.element).chosen('destroy');
                $('select', this.element).chosen();
            }
        },

        val: function () {
            if (!this.$select.val()) {
                return [];
            }

            return [this.$select.val()];
        },

        setOptions: function (options) {
            $('option', this.$select).remove();

            const $option = $('<option></option>')
                .attr('value', '');
            this.$select.append($option);

            _.each(options, function (option) {
                const $option = $('<option></option>')
                    .attr('value', option['url_key'])
                    .html(option['name']);
                this.$select.append($option);
            }.bind(this));
        }
    });

    return $.mst.finderFilter;
});
