/**
 * Copyright © magebig.com. All Rights Reserved.
 */
define([
    "jquery"
], function ($) {
    "use strict";

    $.widget('magebig.MbCollapse', {
        options: {
            id: '#mb-collapsible',
            accordion: true,
            speed: 300,
            mouseType: 0,
            collapsed: 'collapsed',
            expanded: 'expanded'
        },

        _create: function () {
            var opts = this.options;
            var $elm = this.element;
            $elm.find("li").each(function () {
                if ($(this).find("ul").length) {
                    var $firstLink = $(this).find("a:first");
                    if (!$firstLink.next().hasClass('ex-coll')) {
                        $firstLink.after("<span class='ex-coll " + opts.collapsed + "'></span>");
                    }
                    if ($firstLink.attr('href') == "#") {
                        $firstLink.click(function () {
                            $(this).next().trigger('click');
                            return false;
                        });
                    }
                }
            });
            $elm.find("li.active").each(function () {
                $(this).parents("ul").slideDown(opts.speed);
                $(this).parents("ul").parent("li").find("a:first").next().removeClass(opts.collapsed).addClass(opts.expanded);
                $(this).find("ul:first").slideDown(opts.speed);
                $(this).find("a:first").next().removeClass(opts.collapsed).addClass(opts.expanded);
            });
            if (opts.mouseType == 0) {
                var $elmCl = $elm.find("li span.ex-coll");
                $elmCl.on('click', function () {
                    var $self = $(this),
                        $parent = $self.parent(),
                        $children = $parent.find("ul:first");
                    if ($parent.find("ul").length) {
                        if (opts.accordion) {
                            //Do nothing when the list is open
                            if (!$parent.find("ul").is(':visible')) {
                                var parents = $(this).parents("ul");
                                var visible = $elm.find("ul:visible");
                                visible.each(function (visibleIndex) {
                                    var close = true;
                                    parents.each(function (parentIndex) {
                                        if (parents[parentIndex] == visible[visibleIndex]) {
                                            close = false;
                                            return false;
                                        }
                                    });
                                    if (close) {
                                        if ($parent.find("ul") != visible[visibleIndex]) {
                                            $(visible[visibleIndex]).slideUp(opts.speed, function () {
                                                $(this).parent("li").find("a:first").next().removeClass(opts.expanded).addClass(opts.collapsed);
                                            });
                                        }
                                    }
                                });
                            }
                        }
                        if ($children.is(":visible")) {
                            $children.slideUp(opts.speed, function () {
                                $self.delay(opts.speed + 1000).removeClass(opts.expanded).addClass(opts.collapsed);
                            });
                        } else {
                            $children.slideDown(opts.speed, function () {
                                $self.delay(opts.speed + 1000).removeClass(opts.collapsed).addClass(opts.expanded);
                            });
                        }
                    }
                })

            }
            if (opts.mouseType > 0) {
                $elm.find("li a").mouseenter(function () {
                    if ($(this).parent().find("ul").length) {
                        if (opts.accordion) {
                            if (!$(this).parent().find("ul").is(':visible')) {
                                var parents = $(this).parents("ul");
                                var visible = $elm.find("ul:visible");
                                visible.each(function (visibleIndex) {
                                    var close = true;
                                    parents.each(function (parentIndex) {
                                        if (parents[parentIndex] == visible[visibleIndex]) {
                                            close = false;
                                            return false;
                                        }
                                    });
                                    if (close) {
                                        if ($(this).parent().find("ul") != visible[visibleIndex]) {
                                            $(visible[visibleIndex]).slideUp(opts.speed, function () {
                                                $(this).parent("li").find("a:first").next().addClass(opts.collapsed);
                                            });
                                        }
                                    }
                                });
                            }
                        }
                        if ($(this).parent().find("ul:first").is(":visible")) {
                            $(this).parent().find("ul:first").slideUp(opts.speed, function () {
                                $(this).parent("li").find("a:first").next().delay(opts.speed + 1000).removeClass(opts.expanded).addClass(opts.collapsed);
                            });
                        } else {
                            $(this).parent().find("ul:first").slideDown(opts.speed, function () {
                                $(this).parent("li").find("a:first").next().delay(opts.speed + 1000).removeClass(opts.collapsed).addClass(opts.expanded);
                            });
                        }
                    }
                });
            }
        }
    });

    return $.magebig.MbCollapse;
});
