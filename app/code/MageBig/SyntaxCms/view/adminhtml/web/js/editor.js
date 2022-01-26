define([
    'jquery',
    'ko',
    'Magento_Ui/js/lib/view/utils/async',
    'mage/adminhtml/events',
    'MageBig_SyntaxCms/cm/lib/codemirror',
    'mage/translate',
    'MageBig_SyntaxCms/cm/addon/hint/show-hint',
    'MageBig_SyntaxCms/cm/addon/hint/css-hint',
    'MageBig_SyntaxCms/cm/addon/hint/html-hint',
    'MageBig_SyntaxCms/cm/addon/hint/magento-hint',
    'MageBig_SyntaxCms/cm/addon/display/fullscreen',
    'MageBig_SyntaxCms/cm/mode/htmlmixed/htmlmixed',
    'MageBig_SyntaxCms/cm/mode/magento/magento',
    'MageBig_SyntaxCms/cm/addon/search/search',
    'MageBig_SyntaxCms/cm/addon/search/searchcursor',
    'MageBig_SyntaxCms/cm/addon/search/jump-to-line',
    'MageBig_SyntaxCms/cm/addon/edit/matchtags',
    'MageBig_SyntaxCms/cm/addon/edit/closebrackets',
    'MageBig_SyntaxCms/cm/addon/fold/foldcode',
    'MageBig_SyntaxCms/cm/addon/fold/foldgutter',
    'MageBig_SyntaxCms/cm/addon/fold/brace-fold',
    'MageBig_SyntaxCms/cm/addon/fold/indent-fold',
    'MageBig_SyntaxCms/cm/keymap/sublime'
], function ($, ko, async, varienGlobalEvents, CodeMirror, $t) {
    'use strict';

    return function (config, element) {
        var that = this,
            elmList = config.elementList,
            elmArray = JSON.parse(elmList);

        var initWinWidget = function () {
            function baseName(str) {
                var base = new String(str).substring(str.lastIndexOf('/') + 1);
                return base;
            }

            if (typeof (window.MediabrowserUtility) !== 'undefined') {
                var huckopenDlg = window.MediabrowserUtility.openDialog;
                var lastSelectName = '';
                var getMediaSrc = function (targetId) {
                    var elem = document.getElementById(targetId);
                    if (elem.snm && elem.snm.cm && elem.snm.cm_enabled) {
                        var cmdoc = elem.snm.cm.doc;
                        var widgetCode = elem.snm.cm.doc.getSelection();
                        if (widgetCode.indexOf('<img ') != -1) {
                            var start = cmdoc.getCursor("from");
                            var sc = cmdoc.getSearchCursor('{{media ', start);
                            if (sc.findNext()) {
                                var from = sc.from();
                                sc = cmdoc.getSearchCursor('}}', from);
                                if (sc.findNext()) {
                                    widgetCode = cmdoc.getRange(from, sc.to());
                                    return widgetCode;
                                    // cmdoc.setSelection(from, sc.to());
                                }
                            }
                        } else {
                            var start = cmdoc.getCursor("from");
                            var sc = cmdoc.getSearchCursor('{{media ', start);
                            if (sc.findPrevious()) {
                                var from = sc.from();

                                sc = cmdoc.getSearchCursor('}}', from);
                                if (sc.findNext()) {
                                    var cmp = CodeMirror.cmpPos(sc.to(), start);
                                    if (cmp > 0) {
                                        widgetCode = cmdoc.getRange(from, sc.to());
                                        return widgetCode;
                                        // cmdoc.setSelection(from, sc.to());
                                    }
                                }
                            }
                        }
                    }
                    return false;
                }
                window.MediabrowserUtility.openDialog = function (url, width, height, title, options) {
                    var path = new RegExp('\/current_tree_path/([^\/]*)\/');
                    var targetId = new RegExp('\/target_element_id/([^\/]*)\/');
                    targetId = url.match(targetId);
                    lastSelectName = '';
                    if (targetId !== null && targetId.length == 2) {
                        var media = getMediaSrc(targetId[1]);
                        if (media !== false) {
                            media = media.replace(/&quot;/g, "'").replace(/"/g, "'");
                            var urlReg = new RegExp("url='([^']*)'");
                            var mediaUrl = media.match(urlReg);
                            if (mediaUrl != null && mediaUrl.length == 2) {
                                var x = url.match(path);
                                if (x != null && x.length == 2) {
                                    var p1 = Base64.encode(mediaUrl[1]);
                                    p1 = p1.replace(/\+/g, ':').replace(/\//g, '_').replace(/\=/g, '-');
                                    url = url.replace(x[0], '/current_tree_path/' + p1 + '/');
                                    lastSelectName = baseName(mediaUrl[1]);
                                }
                            }
                        }
                    }
                    var result = huckopenDlg.call(this, url, width, height, title, options);
                    return result;
                };

                $.widget("mage.mediabrowser", $.mage.mediabrowser, {
                    loadFileList: function (node, uploaded) {
                        var contentBlock = this.element.find('#contents');
                        contentBlock.on('contentUpdated', function () {
                            try {
                                if (lastSelectName) {
                                    var p1 = Base64.encode(lastSelectName);
                                    p1 = p1.replace(/\,/g, '-').replace(/\+/g, ':').replace(/\//g, '_').replace(/\=/g, '-');
                                    $('#' + p1).click();
                                }
                            } catch (e) {
                            }
                        });
                        return this._super(node, uploaded);
                    },
                    insertAtCursor: function (elem, value) {
                        if (elem.snm && elem.snm.cm && elem.snm.cm_enabled) {
                            var cmdoc = elem.snm.cm.doc;
                            var widgetCode = elem.snm.cm.doc.getSelection();
                            if (widgetCode.indexOf('<img ') != -1) {
                                var start = cmdoc.getCursor("from");
                                var sc = cmdoc.getSearchCursor('<img ', start);
                                if (sc.findNext()) {
                                    var from = sc.from();
                                    sc = cmdoc.getSearchCursor('>', from);
                                    if (sc.findNext()) {
                                        widgetCode = cmdoc.getRange(from, sc.to());
                                        cmdoc.setSelection(from, sc.to());
                                    }
                                }
                            } else {
                                var start = cmdoc.getCursor("from");
                                var sc = cmdoc.getSearchCursor('<img ', start);
                                if (sc.findPrevious()) {
                                    var from = sc.from();

                                    sc = cmdoc.getSearchCursor('>', from);
                                    if (sc.findNext()) {
                                        var cmp = CodeMirror.cmpPos(sc.to(), start);
                                        if (cmp > 0) {
                                            widgetCode = cmdoc.getRange(from, sc.to());
                                            cmdoc.setSelection(from, sc.to());
                                        }
                                    }
                                }
                            }
                            value = value.replace(/&quot;/g, "'");
                            elem.snm.cm.doc.replaceSelection(value);
                            return;
                        }
                        return this._super(elem, value);
                    }
                });
            }

            if (typeof (window.WysiwygWidget) !== 'undefined') {
                var Widget_updateContent = window.WysiwygWidget.Widget.prototype.updateContent;
                window.WysiwygWidget.Widget.prototype.updateContent = function (content) {
                    var elem = document.getElementById(this.widgetTargetId);
                    if (elem.snm && elem.snm.cm && elem.snm.cm_enabled) {
                        elem.snm.cm.doc.replaceSelection(content);
                        return;
                    }
                    Widget_updateContent.call(this, content);
                };

                var Widget_initOptionValues = window.WysiwygWidget.Widget.prototype.initOptionValues;
                window.WysiwygWidget.Widget.prototype.initOptionValues = function () {
                    if (!this.wysiwygExists()) {
                        var elem = document.getElementById(this.widgetTargetId);
                        if (elem.snm && elem.snm.cm && elem.snm.cm_enabled) {
                            var cmdoc = elem.snm.cm.doc;
                            var widgetCode = elem.snm.cm.doc.getSelection();

                            if (widgetCode.indexOf('{{widget') != -1) {
                                var start = cmdoc.getCursor("from");
                                var sc = cmdoc.getSearchCursor('{{', start);
                                if (sc.findNext()) {
                                    var from = sc.from();
                                    sc = cmdoc.getSearchCursor('}}', from);
                                    if (sc.findNext()) {
                                        widgetCode = cmdoc.getRange(from, sc.to());
                                        cmdoc.setSelection(from, sc.to());
                                    }
                                }
                            } else {
                                var start = cmdoc.getCursor("from");
                                var sc = cmdoc.getSearchCursor('{{', start);
                                if (sc.findPrevious()) {
                                    var from = sc.from();

                                    sc = cmdoc.getSearchCursor('}}', from);
                                    if (sc.findNext()) {
                                        var cmp = CodeMirror.cmpPos(sc.to(), start);
                                        if (cmp > 0) {
                                            widgetCode = cmdoc.getRange(from, sc.to());
                                            cmdoc.setSelection(from, sc.to());
                                        }
                                    }
                                }
                            }
                            if (widgetCode.indexOf('{{widget') != -1) {
                                this.optionValues = new Hash({});
                                widgetCode.gsub(/([a-z0-9\_]+)\s*\=\s*[\"]{1}([^\"]+)[\"]{1}/i, function (match) {
                                    if (match[1] == 'type') {
                                        this.widgetEl.value = match[2];
                                    } else {
                                        this.optionValues.set(match[1], match[2]);
                                    }
                                }.bind(this));
                                this.loadOptions();

                                window.setTimeout(function () {
                                    $(this.widgetEl).trigger('change');
                                }.bind(this), 0);
                            }
                        }
                        return;
                    }
                    Widget_initOptionValues.call(this);
                }
            }

            if (typeof (window.Variables) !== 'undefined') {
                var Variables_insertVariable = window.Variables.insertVariable;
                window.Variables.insertVariable = function (variable) {
                    var elem = document.getElementById(this.textareaElementId);
                    if (elem.snm && elem.snm.cm && elem.snm.cm_enabled) {
                        var windowId = this.dialogWindowId;
                        $('#' + windowId).modal('closeModal');

                        if (!isNaN(variable)) {
                            elem.snm.cm.doc.replaceSelection(Variables.variablesValue[variable - 1]);
                        } else {
                            elem.snm.cm.doc.replaceSelection(variable);
                        }

                        return;
                    }
                    Variables_insertVariable.call(this, variable);
                }
            }
        }

        var detachKO = function (cm) {
            var elm = cm.display.input.textarea;
            if (elm) {
                ko.cleanNode(elm);
                /*
                 var ka = ko.dataFor(elm);
                 var context = ko.contextFor(elm)
                 */
            }
        };

        var ignoreevent = false;

        var toogleCm = function (elem, mode, show, options, mceHeight) {
            if (!show) {
                if (elem.snm && elem.snm.cm) {
                    $(elem.snm.cm.display.wrapper).parent().hide();
                    //elem.snm.cm.display.wrapper.style.display = 'none';
                    //elem.snm.cm=null;
                    elem.snm.cm_enabled = false;
                }
                var cnt = $(elem).parent();
                var editor = cnt.find('span.mceEditor');
                if (editor.length) {
                    editor.show();
                    $(elem).hide();
                    return;
                }
                editor = cnt.find('div.mce-tinymce');
                if (editor.length) {
                    editor.show();
                    $(elem).hide();
                    return;
                }
                $(elem).show();
                return;
            }

            if (!elem.snm || !elem.snm.cm) {
                elem.style.display = "none";
                var eopt = $.extend(options, {
                    lineNumbers: true,
                    lineWrapping: true,
                    value: elem.value,
                    mode: mode,
                    indentUnit: 2,
                    matchTags: {bothTags: true},
                    keyMap: "sublime",
                    autoCloseBrackets: true,
                    matchBrackets: true,
                    showCursorWhenSelecting: true,
                    foldGutter: true,
                    gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                    extraKeys: {
                        "Ctrl-Space": "autocomplete",
                        "Ctrl-J": "toMatchingTag",
                        "F11": function (cm) {
                            cm.setOption("fullScreen", !cm.getOption("fullScreen"));
                        },
                        "Esc": function (cm) {
                            if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
                        }
                    }
                });
                var cm = CodeMirror(function (elt) {
                    $("<div class='snm-cm-wrap'></div>").append(elt).insertAfter(elem);
                }, eopt);

                var wrapMn = $(cm.display.wrapper).parent(),
                    note = $t('Press F11 for full screen editing'),
                    noteHtml = '<span class="note">' + note + '</span>';

                wrapMn.prepend(noteHtml);

                wrapMn.resizable({
                    resize: function () {
                        cm.setSize(null, wrapMn.height() + "px");
                    }
                });

                $(cm.display.wrapper).height(mceHeight);

                cm.execCommand('selectAll');
                cm.indentSelection("smart");
                cm.setCursor(1);

                var charWidth = cm.defaultCharWidth(), basePadding = 4;
                cm.on("renderLine", function (cm, line, elt) {
                    var off = CodeMirror.countColumn(line.text, null, cm.getOption("tabSize")) * charWidth;
                    elt.style.textIndent = "-" + off + "px";
                    elt.style.paddingLeft = (basePadding + off) + "px";
                });
                cm.refresh();

                if ($(elem).hasClass('snm-cm-update-onblur')) {
                    cm.on('blur', function (cm, object) {
                        if (cm.snm_source && !ignoreevent) {
                            detachKO(cm);
                            cm.snm_source.value = cm.getValue();
                            $(cm.snm_source).change();
                            detachKO(cm);
                            cm.refresh()
                        }

                    });
                } else {
                    cm.on('change', function (cm, object) {
                        if (cm.snm_source && !ignoreevent) {
                            detachKO(cm);
                            cm.snm_source.snm.lastValue = cm.getValue();
                            cm.snm_source.value = cm.getValue();

                            $(cm.snm_source).change();
                            detachKO(cm);
                            cm.refresh()
                        }
                        ignoreevent = false;
                    });
                }
                cm.snm_source = elem;
                elem.snm = elem.snm || {};
                elem.snm.cm = cm;
                elem.snm.cm_enabled = true;
                elem.snm.lastValue = elem.value;

                detachKO(cm);

                window.setTimeout(function () {
                    cm.refresh()
                }, 500);

                $('body').on('processStop', function () {
                    if (elem.snm.lastValue !== elem.value) {
                        elem.snm.lastValue = elem.value;
                        elem.snm.cm.setValue(elem.value);
                    }
                });
            } else {
                elem.style.display = "none";
                $(elem.snm.cm.display.wrapper).parent().show();
                ignoreevent = true;
                elem.snm.cm.setValue(elem.value);
                elem.snm.cm.execCommand('selectAll');
                elem.snm.cm.indentSelection("smart");
                elem.snm.cm.setCursor(1);

                elem.snm.cm.clearHistory();
                elem.snm.cm_enabled = true;
            }
        };

        var activedCM = function (elem, mode, options) {
            if (elem.snm && elem.snm.initcm) {
                return;
            }

            if (!elem.snm)
                elem.snm = {};
            elem.snm.initcm = true;

            var cnt = $(elem).parent();
            var mceHeight = $(elem).height();
            var mceE = cnt.find('.mceLayout');
            var mce4 = cnt.find('.mce-tinymce');

            if (mceHeight < 300) {
                mceHeight = 300;
            }

            $(elem).addClass('hide-textarea');

            var isMceActive = mceE.length || mce4.length;
            isMceActive = isMceActive || cnt.find('.buttons-set button.add-variable').is(":hidden") || cnt.find('.buttons-set button.action-add-image').is(":hidden");

            cnt.find('.buttons-set button.action-show-hide').on('click', function (event) {
                if (!elem.snm || !elem.snm.cm_enabled) {
                    setTimeout(function () {
                        toogleCm(elem, mode, true, options, mceHeight);
                    }, 0);
                } else {
                    toogleCm(elem, mode, false, options, mceHeight);
                }
            });

            if (!isMceActive) {
                toogleCm(elem, mode, true, options, mceHeight);
            } else {
                toogleCm(elem, mode, false, options, mceHeight);
            }

            varienGlobalEvents.attachEventHandler('wysiwygEditorInitialized', function (target) {
                var height = $(target.editorContainer).parent().height() + 'px';
                var heightEditor = $(target.editorContainer).height() + 'px';

                $(target.editorContainer).parent().css("min-height", height);

                if (elem.snm && elem.snm.cm) {
                    $(elem.snm.cm.display.wrapper).css("height", heightEditor);
                }
            }.bind(this));
        };

        elmArray.forEach(function (selector) {
            async.async(selector.selector, function (elem) {
                activedCM(elem, selector.type, config.editorOption);
                initWinWidget();
            });
        });
    }
});
