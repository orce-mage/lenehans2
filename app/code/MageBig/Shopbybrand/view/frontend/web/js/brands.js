/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery', 'jquery-ui-modules/autocomplete'], function ($) {
    $.widget('magebig.alphabetList', {
        options: {
            charList: '[data-role="char-list"]',
            brandList: '[data-role="brand-list-index"]',
            charItem: '[data-char]',
            item: '[data-label]',
            noItemLabel: '.no-item',
            sameHeight: '.item-bottom',
        },
        _create: function () {
            var self = this, conf = this.options;
            this._assignVariables();
            this._arrangeList();
            self.element.removeClass('no-loaded');
            self.element.find('.brand-inner').removeClass('hidden');
            self._lazyImage();
            self._sameHeight();
            var winWidth = window.innerWidth, t = false;
            $(window).on('resize', function () {
                if (window.innerWidth != winWidth) {
                    if (t) {
                        clearTimeout(t);
                    }
                    t = setTimeout(function () {
                        self._sameHeight();
                    }, 300);
                    winWidth = window.innerWidth;
                }
            });
        },
        _lazyImage: function () {
            var self = this, conf = this.options;
            self.element.find('[data-src]').each(function () {
                var $img = $(this);
                $img.attr('src', $img.data('src'));
            });
        },
        _sameHeight: function () {
            var self = this, conf = this.options;
            self.element.find('.brand-group').each(function () {
                var maxHeight = 0, $group = $(this);
                $group.find(conf.sameHeight).css({minHeight: ''}).each(function () {
                    var $sItem = $(this);
                    var height = $sItem.outerHeight();
                    if (height > maxHeight) {
                        maxHeight = height;
                    }
                }).css({minHeight: maxHeight});
            });
        },
        _assignVariables: function () {
            var self = this, conf = this.options;
            self.$charList = self.element.find(conf.charList);
            self.$brandList = self.element.find(conf.brandList);
            self.$items = self.element.find(conf.item);
            self.$charItem = self.element.find(conf.charItem);
            self.$noItemLabel = self.element.find(conf.noItemLabel);
            self.brandGroups = {};
            self.$items.each(function () {
                var $item = $(this);
                var firstChar = $item.data('label')[0];
                if (typeof self.brandGroups[firstChar] == 'undefined') {
                    self.brandGroups[firstChar] = [];
                }
                self.brandGroups[firstChar].push($item);
            });
            self._filterList();
        },
        _arrangeList: function () {
            var self = this, conf = this.options;
            $.each(self.brandGroups, function (character, el) {
                $.each(self.brandGroups[character], function (i, $item) {
                    if (!isNaN(character)) {
                        $item.attr('data-group', 'num');
                    } else {
                        $item.attr('data-group', character);
                    }
                });
                self.$charList.find('[data-char="' + character + '"]').addClass('available');
            });
            self.$charList.find('[data-char=all]').addClass('available');
            var $target;
            $target = self.element.find('[data-group="num"]');
            if ($target.length) {
                self.$charList.find('[data-char=num]').addClass('available');
            }
        },
        _filterList: function () {
            var self = this, conf = this.options;
            self.element.find('[data-char]').click(function (e) {
                e.preventDefault();
                var $char = $(this), character = $char.data('char');
                if (!$char.hasClass('available')) {
                    return true;
                }
                $char.addClass('active').siblings().removeClass('active');
                if (character == 'all') {
                    self.element.find('[data-group]').show();
                    self.$noItemLabel.addClass('d-none');
                } else if (character == 'num') {
                    var $target = self.element.find('[data-group="num"]');
                    if ($target.length) {
                        $target.siblings().hide();
                        $target.show();
                        self.$noItemLabel.addClass('d-none');
                    } else {
                        self.element.find('[data-group]').hide();
                        self.$noItemLabel.removeClass('d-none');
                    }
                } else {
                    var $target = self.element.find('[data-group="' + character + '"]');
                    if ($target.length) {
                        $target.siblings().hide();
                        $target.show();
                        self.$noItemLabel.addClass('d-none');
                    } else {
                        self.element.find('[data-group]').hide();
                        self.$noItemLabel.removeClass('d-none');
                    }
                }
                self._sameHeight();
            });
        }
    });
    $.widget('magebig.searchBrands', {
        options: {
            input: '[data-role=brand_search_input]',
            sourceUrl: false,
            brandList: [],
            appendTo: '[data-role=brand-list-wrap]',
            brandUrl: false,
        },
        _create: function () {
            var self = this, conf = this.options;
            this.$input = $(conf.input, self.element);
            this.$appendTo = $(conf.appendTo, self.element);
            $.ajax({
                url: conf.brandUrl,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    self.element.removeClass('hidden');
                    self.$input.autocomplete({
                        source: res,
                        appendTo: self.$appendTo,
                        autoFocus: true,
                        messages: {
                            noResults: '',
                            results: function (amount) {
                                return '';
                            }
                        },
                        focus: function (event, ui) {
                            var $a = $('.ui-state-focus', self.$appendTo);
                            $a.parents('.item').first().addClass('selected').siblings().removeClass('selected');
                        }
                    });
                    var uiAutocomplete = self.$input.data('uiAutocomplete');
                    uiAutocomplete._renderItem = function (ul, item) {
                        ul.addClass('brand-list');
                        var label = item.label, inputText = self.$input.val();
                        if (inputText) {
                            var re = new RegExp(inputText, "gi");
                            label = label.replace(re, function (match) {
                                return '<strong>' + match + '</strong>';
                            });
                        }
                        var html = '';
                        html += '<a href="' + item.url + '">';
                        html += '<span class="brand-image"><img src="' + item.img + '" /></span>';
                        html += '<span class="brand-title">' + label + '</span>';
                        html += '</a>';
                        return $('<li class="item">')
                            .append(html)
                            .appendTo(ul);
                    };
                    uiAutocomplete.__responseOld = uiAutocomplete.__response;
                    uiAutocomplete.__response = function (content) {
                        var that = uiAutocomplete;
                        that.__responseOld(content);
                        if (content && content.length) {
                            that.liveRegion.addClass('has-items');
                            self.$appendTo.find('.brand-list').removeClass('_hide');
                        } else {
                            self.$appendTo.find('.brand-list').addClass('_hide');
                            self.$appendTo.find('li.selected').removeClass('selected');
                            that.liveRegion.removeClass('has-items');
                        }
                    }
                }
            });
            this.$input.on('focus', function () {
                $('.brand-list', self.element).show();
                if ($('.has-items', self.element).length) {
                    $('.brand-list', self.element).removeClass('_hide');
                }
            }).on('blur', function () {
                //if (self.$input.val() == '') {
                self.$appendTo.find('.brand-list').addClass('_hide');
                self.element.find('.ui-helper-hidden-accessible').text('');
                //}
            });
        }
    });

    $.widget('magebig.brands', {
        options: {},
        _create: function () {
            var self = this;
            $.each(this.options, function (fn, options) {
                var namespace = fn.split(".")[0];
                var name = fn.split(".")[1];
                if (typeof $[namespace] !== 'undefined') {
                    if (typeof $[namespace][name] !== 'undefined') {
                        $[namespace][name](options, self.element);
                    }
                }
            });
        }
    });
    return $.magebig.brands;
});
