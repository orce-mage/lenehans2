/**
 * Copyright Â© 2018 MageBig. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'nanoscroller'
], function ($) {

    !function (o) {
        if (o.support.touch = "ontouchend" in document, o.support.touch) {
            var t, e = o.ui.mouse.prototype, u = e._mouseInit, n = e._mouseDestroy;

            function c(o, t) {
                if (!(o.originalEvent.touches.length > 1)) {
                    o.preventDefault();
                    var e = o.originalEvent.changedTouches[0], u = document.createEvent("MouseEvents");
                    u.initMouseEvent(t, !0, !0, window, 1, e.screenX, e.screenY, e.clientX, e.clientY, !1, !1, !1, !1, 0, null), o.target.dispatchEvent(u)
                }
            }

            e._touchStart = function (o) {
                !t && this._mouseCapture(o.originalEvent.changedTouches[0]) && (t = !0, this._touchMoved = !1, c(o, "mouseover"), c(o, "mousemove"), c(o, "mousedown"))
            }, e._touchMove = function (o) {
                t && (this._touchMoved = !0, c(o, "mousemove"))
            }, e._touchEnd = function (o) {
                t && (c(o, "mouseup"), c(o, "mouseout"), this._touchMoved || c(o, "click"), t = !1)
            }, e._mouseInit = function () {
                var t = this;
                t.element.bind({
                    touchstart: o.proxy(t, "_touchStart"),
                    touchmove: o.proxy(t, "_touchMove"),
                    touchend: o.proxy(t, "_touchEnd")
                }), u.call(t)
            }, e._mouseDestroy = function () {
                var t = this;
                t.element.unbind({
                    touchstart: o.proxy(t, "_touchStart"),
                    touchmove: o.proxy(t, "_touchMove"),
                    touchend: o.proxy(t, "_touchEnd")
                }), n.call(t)
            }
        }
    }($);

    $.widget('magebig.ajaxfilter', {
        options: {
            enableAjax: true,
            ajaxSelector: '.filter-current a.action.remove, .filter-options-content a, a.filter-clear, .toolbar-products .pages-items a'
        },

        _create: function () {
            var self = this, conf = this.options;
            this._initBlockHtml();
            this._defaultEvents();
            setTimeout(function () {
                self._updateToolbar();
            }, 500);

            this._searchTextFilter();

            if (typeof window.history.replaceState === "function") {
                window.history.replaceState({url: document.URL}, document.title);

                setTimeout(function () {
                    window.onpopstate = function (e) {
                        if (e.state) {
                            self._ajaxFilter(e.state.url, true, false);
                        }
                    };
                }, 0)
            }
        },

        _initBlockHtml: function () {
            var self = this, conf = this.options;

            $('.filter-options-item .filter-options-overflow').addClass('nano-content').wrap('<div class="nano"></div>');
            $('.filter-options-item .nano').nanoScroller();

            this._cloneFilterClear();

            $('[data-role=filter-dropdown]').on('change', function () {
                var $select = $(this),
                    actionUrl = $select.val();
                self.activeCode = $select.data('code');
                self._ajaxFilter(actionUrl, true, true);
            });
            $('[data-role=mb-filter-checkbox] [type=checkbox]').on('change', function () {
                var $checkbox = $(this),
                    $elm = $checkbox.parents('[data-role=mb-filter-checkbox]').first(),
                    code = $elm.data('code'),
                    actionUrl = $elm.data('action'),
                    multiSelect = $elm.data('select'),
                    value = [];
                if (multiSelect) {
                    $elm.find('[type=checkbox]:checked').each(function () {
                        value.push($(this).val());
                    });
                } else if ($checkbox.is(':checked')) {
                    value.push($checkbox.val());
                    $elm.find('[type=checkbox]:checked').each(function () {
                        var $this = $(this);
                        if (!$this.is($checkbox)) {
                            $this.prop('checked', false);
                        }
                    });
                }
                if (value.length) {
                    value = value.join(',');
                    actionUrl += (actionUrl.search(/\?/) != -1) ? '&' : '?';
                    actionUrl += code + '=' + value;
                }
                self.activeCode = code;
                self._ajaxFilter(actionUrl, true, true);
            });
        },

        _updateToolbar: function () {
            var self = this,
                currURL = document.URL,
                currURLVars = this.getUrlVars(currURL),
                params = '?',
                $currentUrl = window.location.protocol + '//' + window.location.hostname + window.location.pathname;

            $.each(currURLVars, function (index, value) {
                if (index !== 'p' && index !== 'page') {
                    params += index + '=' + value + '&';
                }
            });

            $('.toolbar.toolbar-products .pages-items a').each(function () {
                var pageUrl = $(this).attr('href'),
                    paging = '',
                    newUrl,
                    urlVars;

                if (pageUrl.indexOf('p=') > -1 || pageUrl.indexOf('page=') > -1) {
                    urlVars = self.getUrlVars(pageUrl);

                    $.each(urlVars, function (index, value) {
                        if (index === 'p' || index === 'page') {
                            paging = index + '=' + value;
                        }
                    });

                    newUrl = $currentUrl + params + paging;
                    $(this).attr('href', newUrl);
                }
            });

            $('.toolbar.toolbar-products').each(function () {
                var $toolbar = $(this);

                setTimeout(function () {
                    var toolbarForm = $toolbar.data('mageProductListToolbarForm');
                    if (toolbarForm) {
                        toolbarForm.changeUrl = function (paramName, paramValue, defaultValue) {
                            var decode = window.decodeURIComponent,
                                urlPaths = this.options.url.split('?'),
                                baseUrl = urlPaths[0],
                                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                                paramData = {},
                                parameters, i;

                            for (i = 0; i < urlParams.length; i++) {
                                parameters = urlParams[i].split('=');
                                paramData[decode(parameters[0])] = parameters[1] !== undefined ?
                                    decode(parameters[1].replace(/\+/g, '%20')) : '';
                            }
                            paramData[paramName] = paramValue;

                            if (paramValue == defaultValue) {
                                delete paramData[paramName];
                            }
                            paramData = $.param(paramData);

                            var actionUrl = baseUrl + (paramData.length ? '?' + paramData : '');
                            self._ajaxFilter(actionUrl, true, true);
                        }
                    }
                }, 500);
            });
        },
        _defaultEvents: function () {
            var self = this, conf = this.options;
            $(conf.ajaxSelector).on('click', function (e) {
                e.preventDefault();
                var $a = $(this);
                var actionUrl = $a.attr('href');
                self._ajaxFilter(actionUrl, true, true);
            });
        },

        getUrlVars: function (url) {
            var vars = {};
            var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
                vars[key] = value;
            });
            return vars;
        },

        _ajaxFilter: function (actionUrl, needSrollTop, pushState) {
            var self = this, conf = this.options;

            if ((!actionUrl) || (actionUrl.search('javascript:') == 0) || (actionUrl.search('#') == 0)) {
                return;
            }

            if (self.getUrlVars(actionUrl)["p"] == self.getUrlVars(window.location.href)["p"] && pushState) {
                var tmp = "p=" + self.getUrlVars(actionUrl)["p"];
                actionUrl = actionUrl.replace(tmp, "");
            }

            if (!self.options.enableAjax) {
                window.location.href = actionUrl;
                return false;
            }

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: {ajax_filter: 1},
                beforeSend: function () {
                    $('body').trigger('processStart');
                },
                success: function (res) {
                    if ($.magnificPopup.instance.isOpen) {
                        $.magnificPopup.close();
                    }
                    if (res.catalog_leftnav) {
                        $('.block.filter').first().replaceWith(res.catalog_leftnav);
                    }
                    if (res.category_products) {
                        res.category_products = decodeURIComponent(res.category_products);
                        var $listContainer = $('#product-container-wrap');
                        $listContainer.html(res.category_products);

                        setTimeout(function () {
                            self._updateToolbar();
                        }, 0);
                    }
                    if (res.page_main_title) {
                        $('.page-title-wrapper').first().replaceWith(res.page_main_title);
                    }

                    var urlState;

                    if (res.updated_url) {
                        urlState = res.updated_url
                    } else {
                        urlState = actionUrl;
                    }

                    if (pushState) {
                        window.history.pushState({url: urlState}, document.title, urlState);
                    } else {
                        var n = history.state;
                        history.replaceState(n, document.title, urlState);
                    }

                    self._initBlockHtml();
                    self._defaultEvents();

                    $('body').trigger('contentUpdated');
                    setTimeout(function () {
                        $('body').trigger('reloadAjaxScroll');
                        if (needSrollTop) {
                            $('body,html').animate({
                                scrollTop: $listContainer.offset().top - 100
                            }, 800);
                        }
                    }, 100);

                    $('body').trigger('processStop');
                },
                error: function (res) {
                    $('body').trigger('processStop');
                    alert('Error in sending ajax request');
                }
            }).done(function (res) {
                $('body').trigger('processStop');
            });
        },

        _cloneFilterClear: function () {
            if (!$('#product-container-wrap .clear-filter-wrap').length) {
                $('#product-container-wrap').prepend('<div class="clear-filter-wrap"></div>');
            }
            if ($('.filter-current').length) {
                var filter = $('.filter-current').clone().addClass('now-filter-clone');
                $('.clear-filter-wrap').html(filter);
            }
        },

        _searchTextFilter: function () {
            var self = this;
            $("input.search-filter").on("keyup", function (ev) {
                var texto = $(this).val(),
                    elm = $(this).parents('.filter-options-wrap');
                self._searchFilter(elm, texto);
            });
        },

        _searchFilter: function (elm, texto) {
            var lista = elm.find('li.item')
                .hide()
                .filter(function () {
                    var item = $(this).text();
                    var padrao = new RegExp(texto, "i");

                    return padrao.test(item);
                })
                .show();

        }

    });
    return $.magebig.ajaxfilter;
});
