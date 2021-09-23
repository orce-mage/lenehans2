/*
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(["jquery",
        "wyomind_MassImportAndUpdate_toolbox",
        "Wyomind_Framework/js/codemirror5/lib/codemirror",
        "Wyomind_Framework/js/codemirror5/addon/selection/active-line",
        "Wyomind_Framework/js/codemirror5/addon/edit/matchbrackets",
        "Wyomind_Framework/js/codemirror5/mode/htmlmixed/htmlmixed",
        "Wyomind_Framework/js/codemirror5/mode/xml/xml",
        "Wyomind_Framework/js/codemirror5/mode/javascript/javascript",
        "Wyomind_Framework/js/codemirror5/mode/css/css",
        "Wyomind_Framework/js/codemirror5/mode/clike/clike",
        "Wyomind_Framework/js/codemirror5/mode/php/php",
        "Wyomind_Framework/js/codemirror5/addon/display/autorefresh",
        "jquery/colorpicker/js/colorpicker",


    ], function (jQuery, toolbox, codeMirror) {
        "use strict";


        return {
            createCodeMirror: function (selector, mode) {

                return codeMirror.fromTextArea(selector, {
                    mode: {
                        name: mode
                    },
                    lint: false,
                    //  lineWrapping: true,
                    styleActiveLine: true,
                    matchBrackets: true,
                    autoCloseBrackets: true,
                    autoCloseTags: true,
                    autoRefresh: true
                })

            },
            data: [],
            initialize:

                function () {

                    var xml_column_mapping = this.createCodeMirror($("xml_column_mapping"), "application/ld+json");

                    function updateTextArea() {
                        xml_column_mapping.save();
                        toolbox.toggleNotification();
                    }

                    xml_column_mapping.on('change', updateTextArea);

                    this.sortable();
                    document.observe('click', function (elt) {
                        if (elt.findElement(".additional-row .link")) {
                            var row = elt.findElement(".additional-row .link").up(".additional-row");
                            this.scope.open(row, this)
                        }
                        if (elt.findElement(".additional-row .details .chevron-down")) {
                            var row = elt.findElement(".additional-row .details").up(".additional-row");
                            this.scope.close(row, this)
                        }
                        if (elt.findElement(".additional-row .details INPUT")) {
                            var row = elt.findElement(".additional-row .details INPUT").up("li.sortable");
                            this.scope.apply(row, this)
                        }
                        if (elt.findElement(".mapping-row .trash")) {
                            var row = elt.findElement(".mapping-row .trash").up("li.sortable");
                            this.row.delete(row, this)
                        }

                        if (elt.findElement(".mapping-row .code")) {
                            var row = elt.findElement(".mapping-row .code").up("li");
                            this.script.open(row, this)
                        }
                        if (elt.findElement("#scripting .validate")) {
                            this.script.validate(this)
                        }
                        if (elt.findElement("#scripting .cancel")) {
                            this.script.cancel(this)
                        }
                        if (elt.findElement("#scripting .clear")) {
                            this.script.clear(this)
                        }
                        if (elt.findElement(".mapping-row .link")) {
                            var row = elt.findElement(".mapping-row .link").up("li");
                            this.row.activate(row, this)
                        }
                        if (elt.findElement(".icon.add")) {
                            this.row.add(this, elt.findElement(".icon.add").ancestors()[2])
                        }
                        if (elt.findElement(".icon.tag")) {
                            this.row.tag(this, elt.findElement(".icon.tag").ancestors()[2])
                        }

                    }.bind(this));


                    document.observe('change', function (elt) {
                        if (elt.findElement("#identifier_code")) {
                            $("identifier").setValue(elt.findElement("#identifier_code").getValue());
                        }
                        if (elt.findElement("#identifier_source")) {
                            $("identifier_offset").setValue(elt.findElement("#identifier_source").getValue());
                        }
                        if (elt.findElement(".mapping-row .attribute")) {
                            var row = elt.findElement(".mapping-row .attribute").up("li");
                            if (elt.findElement(".mapping-row .attribute").select("OPTION")[elt.findElement(".mapping-row .attribute").selectedIndex].hasClassName("storeviews-dependent")) {
                                row.select(".scope-row")[0].removeClassName("hidden");
                            } else {
                                row.select(".scope-row")[0].addClassName("hidden");
                            }
                            if (row.select(".configurableproducts-row").length) {
                                if (elt.findElement(".mapping-row .attribute").select("OPTION")[elt.findElement(".mapping-row .attribute").selectedIndex].hasClassName("no-configurable")) {
                                    row.select(".configurableproducts-row")[0].addClassName("hidden");
                                } else {
                                    row.select(".configurableproducts-row")[0].removeClassName("hidden");
                                }
                            }
                            if (row.select(".importupdate-row").length) {
                                if (elt.findElement(".mapping-row .attribute").select("OPTION")[elt.findElement(".mapping-row .attribute").selectedIndex].hasClassName("no-importupdate")) {

                                    row.select(".importupdate-row")[0].addClassName("hidden");
                                } else {
                                    row.select(".importupdate-row")[0].removeClassName("hidden");
                                }
                            }
                            this.row.optionsShow(row, this)
                            this.row.save(row, this)
                        }
                        if (elt.findElement(".mapping-row .source")) {
                            var row = elt.findElement(".mapping-row .source").up("li");

                            this.row.optionsShow(row, this)

                            this.row.save(row, this)
                        }
                        if (elt.findElement(".tag-input-box INPUT")) {

                            var row = elt.findElement(".tag-input-box INPUT").up("li");
                            this.row.save(row, this)
                        }
                        if (elt.findElement(".mapping-row .default.value")) {
                            var row = elt.findElement(".mapping-row .default").up("li");
                            this.row.save(row, this);
                        }
                        if (elt.findElement("SELECT.default.options")) {
                            var row = elt.findElement("SELECT.default").up("li");
                            this.row.optionUpdate(row, this);
                            this.row.save(row, this);
                        }

                    }.bind(this));

                    toolbox.loadSource(function () {

                    }.bind(this));
                }

            ,
            sortable: function () {
                jQuery('#mapping-area').sortable({
                    handle: '.grip',
                    axis: "y",
                    scroll: true,
                    only: 'sortable',
                    stop: function () {
                        this.save()
                    }.bind(this)
                })
            }
            ,
            updateSource: function () {
                var mapping = $("mapping").getValue().evalJSON();
                var options = new Array;
                options.push("<option value = ''>custom value</option>");
                this.data.each(function (header, i) {
                    options.push("<option value='" + i + "'>" + header + "</option>")
                });

                $$("#mapping-area .source").each(function (src, i) {

                    src.update(options.join(""));
                    src.select("OPTION")[0].selected = true;

                    src.select("OPTION").each(function (option, x) {

                        if (typeof mapping[i].source != "undefined" && mapping[i].source == option.innerText &&  $("has_header").getValue() == 1) {

                            src.select("OPTION")[x].selected = true;
                        } else if (mapping[i].index == option.value && $("has_header").getValue() == 0) {

                            src.select("OPTION")[x].selected = true;
                        }

                    })

                    // if (src.selectedIndex == 0) {

                    this.row.optionsShow(src.up('LI'), this);
                    // }
                }.bind(this));

                $("identifier_source").update(options.join(""));
                var identifier_offset = $("identifier_offset").getValue();
                $("identifier_source").select("OPTION").each(function (option, x) {
                    if (identifier_offset == option.value) {
                        $("identifier_source").select("OPTION")[x].selected = true;
                    }
                });
                $$("LI.sortable").each(function (li) {
                    this.scope.apply(li, this)
                }.bind(this))
            }
            ,
            save: function () {
                var mapping = new Array();
                $$(".aggregate").each(function (aggregate) {
                    if (aggregate.getValue() != "") {
                        mapping.push(aggregate.getValue().evalJSON());
                    }
                });
                $("mapping").setValue(Object.toJSON(mapping));
            }
            ,
            row: {
                optionUpdate: function (row) {

                    if (jQuery(row).find("SELECT.default").val() != "") {
                        jQuery(row).find("INPUT.default.value").val(jQuery(row).find("SELECT.default").val());
                        jQuery(row).find("INPUT.default.value").hide().addClass("invisible");
                        var multiple = jQuery(row).find(".attribute OPTION:selected").attr('data-multiple');
                        if (multiple) {
                            jQuery(row).find("SELECT.default.options").prop("multiple", true);
                        } else {
                            jQuery(row).find("SELECT.default.options").prop("multiple", false);

                        }

                    } else {
                        // jQuery(row).find("INPUT.default.value").val("");
                        jQuery(row).find("INPUT.default.value").show().removeClass("invisible");
                        jQuery(row).find("SELECT.default.options").prop("multiple", false);

                    }
                }
                ,
                optionsShow: function (row, parent) {

                    var prop = jQuery(row).find(".attribute OPTION:selected").attr('data-options');
                    var multiple = jQuery(row).find(".attribute OPTION:selected").attr('data-multiple');
                    var newable = jQuery(row).find(".attribute OPTION:selected").attr('data-newable');
                    var val = jQuery(row).find(".source").val();

                    if (prop != "" && val == "") {
                        var options = atob(prop).evalJSON();

                        jQuery(row).find('.default.options').show().removeClass("invisible");
                        jQuery(row).find('.default.options').html(null);
                        var isSelected = false;
                        var val = jQuery(row).find('.default.value').val();
                        if (multiple) {
                            jQuery(row).find('.default.options').prop("multiple", true);
                        } else {
                            jQuery(row).find('.default.options').prop("multiple", false);
                        }

                        jQuery.each(options, function (key, option) {
                            var selected = "";

                            if ((option == val && !multiple) || (val.split(",").indexOf(option) > -1 && multiple)) {
                                selected = "selected";
                                isSelected = true;
                            }
                            jQuery(row).find('.default.options').append(jQuery("<option " + selected + "/>").val(option).text(option));

                        });

                        if (!isSelected) {
                            jQuery(row).find('.default.value').show().removeClass("invisible");

                            if (newable == "1") {
                                jQuery(row).find('.default.options').append(jQuery("<option />").val('').addClass("add").text("new value").prop("selected", true));
                            }

                        } else {
                            jQuery(row).find('.default.value').hide().addClass("invisible");
                            if (newable == "1") {
                                jQuery(row).find('.default.options').append(jQuery("<option />").val('').addClass("add").text("new value"));
                            }
                            jQuery(row).find('.default.value').val(jQuery(row).find('SELECT.default.options').val());
                        }

                        parent.row.optionUpdate(row, parent);

                    } else {

                        jQuery(row).find('.default.options').html(null).prop("multiple", false);
                        if (val != "") {
                            jQuery(row).find('.default.options').hide();
                            jQuery(row).find('.default.value').show().addClass("invisible");
                            parent.row.optionUpdate(row, parent);
                        } else {

                            jQuery(row).find('.default.options').hide();
                            jQuery(row).find('.default.value').show().removeClass("invisible");
                        }
                    }

                    parent.row.save(row, parent);
                }
                ,
                colorpicker: function () {
                    jQuery('.color').ColorPicker({

                            onShow: function (el) {

                                jQuery(el).fadeIn(500);
                                return false;
                            },
                            onSubmit:
                                function (hsb, hex, rgb, el) {
                                    jQuery(el).parents("LI").css({"background-color": "rgba(" + rgb.r + "," + rgb.g + "," + rgb.b + ",0.5)"})
                                    jQuery(el).ColorPickerHide();
                                    require(["wyomind_MassImportAndUpdate_mapping"], function (mapping) {
                                        mapping.row.save(jQuery(el).parents("LI")[0], mapping);
                                    })
                                }

                        }
                    );
                }
                ,
                add: function (parent, elt) {

                    elt.insert({after: massImportAndUpdateTemplate});

                    var row = elt.next();
                    row.setStyle({"background-color": elt.getStyle("background-color")})
                    var options = new Array;
                    options.push("<option value = ''>custom value</option>");

                    parent.data.each(function (header, i) {
                        options.push("<option value='" + i + "'>" + header + "</option>")
                    });
                    row.select(".source")[0].update(options.join(""));
                    row.select(".source")[0].select("OPTION")[0].selected = true;
                    parent.row.optionsShow(row, parent)
                    parent.scope.apply(row, parent);


                    parent.row.colorpicker(parent, row);
                    parent.row.save(row, parent);
                    parent.sortable()
                }
                ,
                tag: function (parent, elt) {
                    elt.select(".tag-input-box")[0].toggleClassName("invisible");
                    if (!elt.select(".tag-input-box")[0].hasClassName("invisible")) {
                        elt.select(".tag-input-box INPUT")[0].focus()
                    } else {
                        elt.select(".tag-input-box INPUT")[0].setValue("")
                    }
                    parent.row.save(elt, parent);
                }
                ,
                save: function (li, parent) {
                    var aggregate = li.select('.aggregate')[0];
                    var data = {};
                    data.id = li.select('.attribute')[0].getValue();

                    data.label = li.select(".attribute OPTION[value='" + data.id + "']")[0].innerText;
                    data.index = li.select('.source')[0].getValue();
                    data.color = li.getStyle('background-color');
                    data.tag = li.down(".tag-input-box").down("INPUT").getValue();

                    data.source = "";
                    if (data.index) {
                        data.source = li.select('.source OPTION')[Math.round(data.index) + 1].innerText;
                    }

                    data.default = jQuery(li).find(".default.value").val();
                    data.scripting = li.select('.scripting')[0].getValue();
                    if (li.select('.rule')[0]) {
                        data.rule = li.select('.rule')[0].readAttribute("data-value");
                    }
                    var storeviews = new Array();
                    li.select(".scope-details INPUT[type='checkbox']").each(function (input) {
                        if (input.checked) {
                            storeviews.push(input.getValue());
                        }
                    });
                    data.configurable = "0";
                    if ($$("#create_configurable_onthefly").length && $$("#create_configurable_onthefly")[0].getValue() == 1) {
                        li.select(".configurable-details INPUT[type='radio']").each(function (input) {
                            if (input.checked) {
                                data.configurable = input.getValue();
                            }
                        });
                        if (data.id == "ConfigurableProduct/attributes") {
                            data.configurable = "1";
                        }
                    }
                    data.importupdate = "2";
                    if ($$("#profile_method").length && $$("#profile_method")[0].getValue() == 3) {
                        li.select(".importupdate-details INPUT[type='radio']").each(function (input) {
                            if (input.checked) {
                                data.importupdate = input.getValue();
                            }
                        });

                    }


                    data.storeviews = storeviews.uniq();
                    data.enabled = !li.hasClassName("disabled");

                    aggregate.setValue(Object.toJSON(data));

                    parent.save()
                }
                ,
                delete:

                    function (row, parent) {
                        if (confirm("Do you really want to delete this row?")) {
                            row.remove();
                            parent.save()
                        }
                    }

                ,
                activate: function (row, parent) {

                    if (!row.hasClassName("disabled")) {
                        row.addClassName("disabled");

                    } else {

                        row.removeClassName("disabled");
                    }

                    parent.row.save(row, parent)
                }
            }
            ,
            script: {
                row: null,
                open:

                    function (row, parent) {
                        var value = row.select(".scripting")[0].getValue().replace(/__LINE_BREAK__/g, "\n");
                        if (value.trim() == '') {
                            value = "<?php\n /* Your custom script */\n return $self;\n";
                        }
                        $$("#scripting #codemirror")[0].setValue(value);
                        this.editor = parent.createCodeMirror($$("#scripting #codemirror")[0], "application/x-httpd-php-open");

                        parent.script.row = row;
                        $("overlay").setStyle({display: 'block'})
                        jQuery("#scripting").draggable({handle: '.handler'});
                    }

                ,
                clear: function (parent) {
                    this.editor.setValue('');
                    parent.script.validate(parent);
                }
                ,
                validate: function (parent) {
                    if (this.editor) {
                        parent.script.row.select(".scripting")[0].setValue(this.editor.getValue().replace(/(?:\r\n|\r|\n)/g, "__LINE_BREAK__"));
                        if (this.editor.getValue() != "") {
                            parent.script.row.select('.code')[0].addClassName("active");
                            jQuery(parent.script.row).find(".default").addClass("invisible").val();

                        } else {
                            parent.script.row.select('.code')[0].removeClassName("active");
                            if (parent.script.row.hasClassName("sortable")) {
                                if (parent.script.row.select(".source")[0].getValue() == '') {
                                    jQuery(parent.script.row).find(".default".removeClass("invisible")).val();

                                } else {
                                    jQuery(parent.script.row).find(".default").addClass("invisible").val();

                                }
                            }
                        }
                    }
                    if (parent.script.row.hasClassName("sortable")) {
                        parent.row.save(parent.script.row, parent)
                    }
                    parent.script.close(parent)
                }
                ,
                cancel: function (parent) {

                    parent.script.close(parent)
                }
                ,
                close: function (parent) {
                    if (this.editor) {
                        this.editor.setValue('');
                        this.editor.toTextArea();
                    }
                    parent.script.row = null;
                    $("overlay").setStyle({display: 'none'})
                }
            }
            ,
            scope: {
                open: function (row, parent) {
                    row.down('.link').addClassName("hidden");
                    row.down('.details').removeClassName("hidden");
                }
                ,
                close: function (row, parent) {
                    row.down('.link').removeClassName("hidden");
                    row.down('.details').addClassName("hidden");

                }
                ,
                apply: function (row, parent) {
                    var global = new Array();

                    if (row.select('.default-scope')[0].checked) {
                        global.push("Default value");
                    }
                    row.select(".website").each(function (website) {
                        website.select(".store").each(function (store) {
                            var list = website.select('.label_area')[0].innerText + " > ";
                            list += store.select('.label_area')[0].innerText + " > ";
                            var views = new Array();
                            store.select(".store-view").each(function (view) {
                                if (view.select("INPUT[type='checkbox']")[0].checked) {
                                    views.push(view.select('.label_area')[0].innerText);
                                }
                            });
                            if (views.length) {
                                list += views.join(',');
                                global.push(list);
                            }
                        })
                    });
                    if (global.length < 1) {
                        row.select(".default-scope")[0].checked = true;
                        parent.scope.apply(row, parent);
                        return;
                    }
                    row.select(".scope-summary")[0].update(global.join(" | "));


                    var global = new Array();
                    row.select(".configurable-details INPUT[type='radio']").each(function (input) {
                        if (input.checked) {

                            global.push(input.next().innerText);
                        }

                    });
                    if (row.select(".configurable-summary").length) {
                        row.select(".configurable-summary")[0].update(global.join(" | "));
                    }

                    var global = new Array();
                    row.select(".importupdate-details INPUT[type='radio']").each(function (input) {
                        if (input.checked) {

                            global.push(input.next().innerText);
                        }

                    });
                    if (row.select(".importupdate-summary").length) {
                        row.select(".importupdate-summary")[0].update(global.join(" | "));
                    }
                    ;
                    //parent.scope.close(row, parent);
                    parent.row.save(row, parent)

                }
                ,
                reset: function (row, parent) {
                    parent.scope.close(row, parent)
                }
            }
        }
    }
);