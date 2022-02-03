define([
    'jquery',
    'mage/template',
    'text!Amasty_Faq/template/autosuggest/category.html',
    'text!Amasty_Faq/template/autosuggest/question.html',
    'Magento_Ui/js/modal/modal',
    'Magento_Search/form-mini'
], function ($, mageTemplate, categoryTemplate, questionTemplate) {
    'use strict';

    function isEmpty(value) {
        return value.length === 0 || value == null || /^\s+$/.test(value);
    }

    $.widget('mage.amFaqAutoSuggest', $.mage.quickSearch, {
        options: {
            autocomplete: 'off',
            minSearchLength: 3,
            responseFieldElements: 'ul li',
            selectClass: 'selected',
            submitBtn: 'button[type="submit"]',
            searchLabel: '[data-role=minisearch-label]',
            searchSelector: '[data-amfaq-js="search"]',
            isExpandable: null,
            hasOnOutsideClick: false
        },

        _create: function () {
            this._super();
            this.searchForm.on('submit', $.proxy(function () {
                var result = this._onSubmit();
                this._updateAriaHasPopup(false);
                return result; // return false to disable form submit
            }, this));

            this.autosuggestOutsideClick = this.onOutsideClick.bind(this);
        },

        _onSubmit: function (e) {
            var value = this.element.val();

            if (isEmpty(value)) {
                e.preventDefault();
            }

            if (this.responseList.selected) {
                window.location.href = this.responseList.selected.find('.qs-option-url').text();
                return false;
            }
        },

        _onPropertyChange: function () {
            var searchField = this.element,
                clonePosition = {
                    position: 'absolute',
                    width: searchField.outerWidth()
                },
                question = mageTemplate(questionTemplate),
                category = mageTemplate(categoryTemplate),
                dropdown = $('<ul role="listbox"></ul>'),
                value = this.element.val();

            this.submitBtn.disabled = true;

            if (value.length >= parseInt(this.options.minSearchLength, 10)) {
                this.submitBtn.disabled = false;

                if (this.options.url !== '') { //eslint-disable-line eqeqeq
                    $.getJSON(this.options.url, {
                        q: value
                    }, $.proxy(function (data) {
                        if (data.length) {
                            $.each(data, function (index, element) {
                                var html;

                                element.index = index;
                                html = element.category ? question({data: element}) : category({data: element});
                                dropdown.append(html);
                            });

                            this._resetResponseList(true);

                            this.responseList.indexList = this.autoComplete.html(dropdown)
                                .css(clonePosition)
                                .show()
                                .find(this.options.responseFieldElements + ':visible');

                            this.element.removeAttr('aria-activedescendant');

                            if (this.responseList.indexList.length) {
                                this._updateAriaHasPopup(true);
                            } else {
                                this._updateAriaHasPopup(false);
                            }

                            this.responseList.indexList
                                .on('click', function (e) {
                                    this.responseList.selected = $(e.currentTarget);
                                    this.searchForm.trigger('submit');
                                }.bind(this))
                                .on('mouseenter mouseleave', function (e) {
                                    this.responseList.indexList.removeClass(this.options.selectClass);
                                    $(e.target).addClass(this.options.selectClass);
                                    this.responseList.selected = $(e.target);
                                    this.element.attr('aria-activedescendant', $(e.target).attr('id'));
                                }.bind(this))
                                .on('mouseout', function (e) {
                                    if (!this._getLastElement() &&
                                        this._getLastElement().hasClass(this.options.selectClass)) {
                                        $(e.target).removeClass(this.options.selectClass);
                                        this._resetResponseList(false);
                                    }
                                }.bind(this));
                        } else {
                            this._resetResponseList(true);
                            this.autoComplete.hide();
                            this._updateAriaHasPopup(false);
                            this.element.removeAttr('aria-activedescendant');
                        }
                    }, this));
                }
            } else {
                this._resetResponseList(true);
                this.autoComplete.hide();
                this._updateAriaHasPopup(false);
                this.element.removeAttr('aria-activedescendant');
            }

            if (!this.hasOnOutsideClick) {
                $(document).on('click', this.autosuggestOutsideClick);
                this.hasOnOutsideClick = true;
            }
        },

        onOutsideClick: function (e) {
            var parent = $(e.target).closest(this.options.searchSelector);

            if (!parent.length) {
                this.autoComplete.hide().html('');
                $(document).off('click', this.autosuggestOutsideClick);
                this.hasOnOutsideClick = false;
            }
        }
    });

    return $.mage.amFaqAutoSuggest;
});
