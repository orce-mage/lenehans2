define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    $.widget('mst.finderFilter', {
        $label:    null,
        $dropdown: null,

        options: {
            filter_id:   null,
            url_key:     '',
            is_required: true,
            multiselect: false
        },

        _create: function () {
            this.$label = $('[data-label]', this.element);
            this.$dropdown = $('[data-dropdown]', this.element);

            this.$label.on('click', function () {
                if (this._isDisabled()) {
                    return;
                }

                this.$dropdown.toggle();
            }.bind(this));

            this._observeItems();
            this._observeOutboundClick();
        },

        _observeItems: function () {
            $('[data-item]', this.$dropdown).on('click', function (e) {
                const $item = $(e.currentTarget);

                if (!this.options.multiselect) {
                    this._resetSelected();
                }

                $item.toggleClass('_selected');

                this._trigger('_change');
                this._hide();
                this._updateLabel();
            }.bind(this))
        },

        _observeOutboundClick: function () {
            $(document).on('click', function (e) {
                if ($(e.target).closest(this.element).length === 0) {
                    this._hide();
                }
            }.bind(this));
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
            } else {
                $(this.element).removeAttr('disabled')
            }
        },

        _isDisabled: function () {
            return $(this.element).attr('disabled');
        },

        val: function () {
            const value = [];

            _.each($('[data-item]', this.$dropdown), function (item) {
                const $item = $(item);
                if ($item.hasClass('_selected')) {
                    value.push($item.attr('data-value'));
                }
            })

            return value;
        },

        setOptions: function (options) {
            $('[data-item]', this.$dropdown).remove();

            _.each(options, function (option) {
                const $item = $('<div></div>')
                    .attr('data-value', option['url_key'])
                    .attr('data-item', true)
                    .html(option['name']);

                $('[data-items]', this.$dropdown).append($item);
            }.bind(this));

            this._observeItems();
            this._resetSelected();
            this._updateLabel();
        },

        _hide: function () {
            this.$dropdown.hide();
        },

        _updateLabel: function () {
            let label = [];

            _.each($('[data-item]', this.$dropdown), function (item) {
                const $item = $(item);
                if ($item.hasClass('_selected')) {
                    label.push($item.text().trim());
                }
            });

            if (label.length) {
                this.$label.html(label.join(', '));
            } else {
                this.$label.html('Please select...');
            }
        },

        _resetSelected: function () {
            $('[data-item]', this.$dropdown).removeClass('_selected');
        }
    });

    return $.mst.finderFilter;
});
