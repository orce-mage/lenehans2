/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global MediabrowserUtility, FORM_KEY, tinyMceEditors */
/* eslint-disable strict */
define([
    'jquery',
    'wysiwygAdapter',
    'Magento_Ui/js/modal/prompt',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'Magento_Ui/js/modal/modal',
    'jquery/ui',
    'jquery/jstree/jquery.jstree',
    'mage/mage'
], function ($, wysiwyg, prompt, confirm, alert, _) {
    MbMediabrowserUtility = {
        windowId: 'modal_dialog_message',
        modalLoaded: false,
        targetElementId: false,
        pathId: '',

        /**
         * @return {Number}
         */
        getMaxZIndex: function () {
            var max = 0,
                cn = document.body.childNodes,
                i, el, zIndex;

            for (i = 0; i < cn.length; i++) {
                el = cn[i];
                zIndex = el.nodeType == 1 ? parseInt(el.style.zIndex, 10) || 0 : 0; //eslint-disable-line eqeqeq

                if (zIndex < 10000) {
                    max = Math.max(max, zIndex);
                }
            }

            return max + 10;
        },

        /**
         * @param {*} url
         * @param {*} width
         * @param {*} height
         * @param {*} title
         * @param {Object} options
         */
        openDialog: function(btn, url, width, height, title, options) {
            this.$btn = $(btn);
            this.targetParent = $(btn).parents('.admin__field-control').first();
            this.targetElement = this.targetParent.find('.input-image').first();

            var windowId = this.windowId,
                content = '<div class="popup-window magento_message" id="' + windowId + '"></div>',
                self = this;

            if (this.modalLoaded) {

                if (!_.isUndefined(options)) {
                    this.modal.modal('option', 'closed', options.closed);
                }

                this.modal.modal('openModal');
                // this.setTargetElementId(options, url);
                // this.setPathId(url);
                $(window).trigger('reload.MediaGallery');

                return;
            }

            this.modal = $(content).modal($.extend({
                title:  title || 'Insert File...',
                modalClass: 'magento',
                type: 'slide',
                buttons: []
            }, options));

            this.modal.modal('openModal');

            $.ajax({
                url: url,
                cache: false,
                type: 'get',
                context: $('body'),
                showLoader: true

            }).done(function (data) {
                self.modal.html(data.replace('"mediabrowser"','"MageBig_Shopbybrand/js/browser"')).trigger('contentUpdated');
                this.modalLoaded = true;
                // this.setTargetElementId(options, url);
                // this.setPathId(url);
            }.bind(this));

        },

        /**
         * Setter for endcoded path id
         */
        setPathId: function (url) {
            this.pathId = url.match(/(&|\/|%26)current_tree_path(=|\/)([\s\S].*?)(\/|$)/)[3];
        },

        /**
         * Setter for targetElementId property
         *
         * @param {Object} options
         * @param {String} url
         */
        setTargetElementId: function (options, url) {
            this.targetElementId = options && options.targetElementId ?
                options.targetElementId
                : url.match(/\/target_element_id\/([\s\S].*?)\//)[1];
        },

        /**
         * Close dialog.
         */
        closeDialog: function () {
            this.modal.modal('closeModal');
        }
    };

    $.widget("custom.mbMediabrowser", {
        eventPrefix: "mbMediabrowser",
        options: {
            targetElementId: null,
            contentsUrl: null,
            onInsertUrl: null,
            newFolderUrl: null,
            deleteFolderUrl: null,
            deleteFilesUrl: null,
            headerText: null,
            tree: null,
            currentNode: null,
            storeId: null,
            showBreadcrumbs: null,
            hidden: 'no-display'
        },

        /**
         * Proxy creation
         * @protected
         */
        _create: function () {
            this._on({
                'click [data-row=file]': 'selectFile',
                'dblclick [data-row=file]': 'insert',
                'click #new_folder': 'newFolder',
                'click #delete_folder': 'deleteFolder',
                'click #delete_files': 'deleteFiles',
                'click #insert_files': 'insertSelectedFiles',
                'fileuploaddone': '_uploadDone',
                'click [data-row=breadcrumb]': 'selectFolder'
            });

            $(window).on('reload.MediaGallery', $.proxy(this.reload, this));
            this.activeNode = null;
            //tree dont use event bubbling
            this.tree = this.element.find('[data-role=tree]');
            this.tree.on('select_node.jstree', $.proxy(this._selectNode, this));
        },

        /**
         * @param {jQuery.Event} event
         * @param {Object} data
         * @private
         */
        _selectNode: function (event, data) {
            var node = data.rslt.obj.data('node');

            this.activeNode = node;
            this.element.find('#delete_files, #insert_files').toggleClass(this.options.hidden, true);
            this.element.find('#contents').toggleClass(this.options.hidden, false);
            this.element.find('#delete_folder')
                .toggleClass(this.options.hidden, node.id == 'root'); //eslint-disable-line eqeqeq
            this.element.find('#content_header_text')
                .html(node.id == 'root' ? this.headerText : node.text); //eslint-disable-line eqeqeq

            this.drawBreadcrumbs(data);
            this.loadFileList(node);
        },

        /**
         * @return {*}
         */
        reload: function (uploaded) {
            return this.loadFileList(this.activeNode, uploaded);
        },

        /**
         * @param {Object} element
         * @param {*} value
         */
        insertAtCursor: function (element, value) {
            var sel, startPos, endPos, scrollTop;

            if ('selection' in document) {
                //For browsers like Internet Explorer
                element.focus();
                sel = document.selection.createRange();
                sel.text = value;
                element.focus();
            } else if (element.selectionStart || element.selectionStart == '0') { //eslint-disable-line eqeqeq
                //For browsers like Firefox and Webkit based
                startPos = element.selectionStart;
                endPos = element.selectionEnd;
                scrollTop = element.scrollTop;
                element.value = element.value.substring(0, startPos) + value +
                    element.value.substring(startPos, endPos) + element.value.substring(endPos, element.value.length);
                element.focus();
                element.selectionStart = startPos + value.length;
                element.selectionEnd = startPos + value.length + element.value.substring(startPos, endPos).length;
                element.scrollTop = scrollTop;
            } else {
                element.value += value;
                element.focus();
            }
        },

        /**
         * @param {Object} node
         */
        loadFileList: function (node, uploaded) {
            var contentBlock = this.element.find('#contents');

            return $.ajax({
                url: this.options.contentsUrl,
                type: 'GET',
                dataType: 'html',
                data: {
                    'form_key': FORM_KEY,
                    node: node.id
                },
                context: contentBlock,
                showLoader: true
            }).done(function (data) {
                contentBlock.html(data).trigger('contentUpdated');

                if (uploaded) {
                    contentBlock.find('.filecnt:last').click();
                }
            });
        },

        /**
         * @param {jQuery.Event} event
         */
        selectFolder: function (event) {
            this.element.find('[data-id="' + $(event.currentTarget).data('node').id + '"]>a').click();
        },

        /**
         * Insert selected files.
         */
        insertSelectedFiles: function () {
            this.element.find('[data-row=file].selected').trigger('dblclick');
        },

        /**
         * @param {jQuery.Event} event
         */
        selectFile: function (event) {
            var fileRow = $(event.currentTarget);

            fileRow.toggleClass('selected');
            this.element.find('[data-row=file]').not(fileRow).removeClass('selected');
            this.element.find('#delete_files, #insert_files')
                .toggleClass(this.options.hidden, !fileRow.is('.selected'));
            fileRow.trigger('selectfile');
        },

        /**
         * @private
         */
        _uploadDone: function () {
            this.element.find('.file-row').remove();
            this.reload(true);
        },

        /**
         * @param {jQuery.Event} event
         * @return {Boolean}
         */
        insert: function (event) {
            var fileRow = $(event.currentTarget),
                targetEl;

            if (!fileRow.prop('id')) {
                return false;
            }
            targetEl = this.getTargetElement();

            if (!targetEl.length) {
                MbMediabrowserUtility.closeDialog();
                throw "Target element not found for content update";
            }

            return $.ajax({
                url: this.options.onInsertUrl,
                data: {
                    filename: fileRow.attr('id'),
                    node: this.activeNode.id,
                    store: this.options.storeId,
                    'as_is': targetEl.is('textarea') ? 1 : 0,
                    'force_static_path': 1,
                    'form_key': FORM_KEY
                },
                context: this,
                showLoader: true
            }).done($.proxy(function(data) {
                if (typeof data == 'string') {
                    var img = data;
                    var src = '';
                    data = {};
                    img.gsub(/\{\{media(.*?)\}\}/i, function (match) {
                        data.full = match[0];
                        match[0].gsub(/url=\"(.*?)\"/, function(url) {
                            src = url[1];
                        });
                        match[0].gsub(/url=\&quot;(.*?)\&quot;/, function(url) {
                            src = url[1];
                        });
                        data.short = src;
                        data.full = MageBig.mediaUrl + data.short;
                    });

                    if (img.indexOf('/pub/media/') === 0) {
                        src = img.replace('/pub/media/', '');
                        data.short = src;
                        data.full = MageBig.mediaUrl + data.short;
                    } else if (img.indexOf('/media/') === 0) {
                        src = img.replace('/media/', '');
                        data.short = src;
                        data.full = MageBig.mediaUrl + data.short;
                    } else {
                        src = $(img).attr('src');
                        data.full = src;
                        data.short = src.replace(MageBig.mediaUrl, "");
                    }

                }
                if (targetEl.is('textarea')) {
                    this.insertAtCursor(targetEl.get(0), data.short);
                } else {
                    targetEl.val(data.short).trigger('change');
                }
				targetEl.trigger('change');
                MbMediabrowserUtility.closeDialog();
                targetEl.focus();
                targetEl.parents('.control').find('.attached_image img').attr('src', data.full);
            }, this));
        },

        /**
         * Find document target element in next order:
         *  in acive file browser opener:
         *  - input field with ID: "src" in opener window
         *  - input field with ID: "href" in opener window
         *  in document:
         *  - element with target ID
         *
         * return HTMLelement | null
         */
        getTargetElement: function() {
            if (typeof MbMediabrowserUtility.targetElement !== 'undefined') {
                return MbMediabrowserUtility.targetElement;
            } else {
                if (typeof(tinyMCE) != 'undefined' && tinyMCE.get(this.options.targetElementId)) {
                    var opener = this.getMediaBrowserOpener() || window;
                    var targetElementId = tinyMceEditors.get(this.options.targetElementId).getMediaBrowserTargetElementId();
                    return $(opener.document.getElementById(targetElementId));
                } else {
                    return $('#' + this.options.targetElementId);
                }
            }
        },

        /**
         * Return opener Window object if it exists, not closed and editor is active
         *
         * return object | null
         */
        getMediaBrowserOpener: function() {
            if (typeof(tinyMCE) != 'undefined'
                && tinyMCE.get(this.options.targetElementId)
                && typeof(tinyMceEditors) != 'undefined'
                && !tinyMceEditors.get(this.options.targetElementId).getMediaBrowserOpener().closed) {
                return tinyMceEditors.get(this.options.targetElementId).getMediaBrowserOpener();
            } else {
                return null;
            }
        },

        /**
         * New folder.
         */
        newFolder: function () {
            var self = this;

            prompt({
                title: this.options.newFolderPrompt,
                actions: {
                    /**
                     * @param {*} folderName
                     */
                    confirm: function (folderName) {
                        $.ajax({
                            url: self.options.newFolderUrl,
                            dataType: 'json',
                            data: {
                                name: folderName,
                                node: self.activeNode.id,
                                store: self.options.storeId,
                                'form_key': FORM_KEY
                            },
                            context: self.element,
                            showLoader: true
                        }).done($.proxy(function (data) {
                            if (data.error) {
                                alert({
                                    content: data.message
                                });
                            } else {
                                self.tree.jstree(
                                    'refresh',
                                    self.element.find('[data-id="' + self.activeNode.id + '"]')
                                );
                            }
                        }, this));

                        return true;
                    }
                }
            });
        },

        /**
         * Delete folder.
         */
        deleteFolder: function () {
            var self = this;

            confirm({
                content: this.options.deleteFolderConfirmationMessage,
                actions: {
                    /**
                     * Confirm.
                     */
                    confirm: function () {
                        return $.ajax({
                            url: self.options.deleteFolderUrl,
                            dataType: 'json',
                            data: {
                                node: self.activeNode.id,
                                store: self.options.storeId,
                                'form_key': FORM_KEY
                            },
                            context: self.element,
                            showLoader: true
                        }).done($.proxy(function(data) {
                            self.tree.jstree('refresh', self.activeNode.id);
                        }, this));
                    },

                    /**
                     * @return {Boolean}
                     */
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        /**
         * Delete files.
         */
        deleteFiles: function () {
            var self = this;

            confirm({
                content: this.options.deleteFileConfirmationMessage,
                actions: {
                    /**
                     * Confirm.
                     */
                    confirm: function () {
                        var selectedFiles = self.element.find('[data-row=file].selected'),
                            ids = selectedFiles.map(function () {
                                return $(this).attr('id');
                            }).toArray();

                        return $.ajax({
                            url: self.options.deleteFilesUrl,
                            data: {
                                files: ids,
                                store: self.options.storeId,
                                'form_key': FORM_KEY
                            },
                            context: self.element,
                            showLoader: true
                        }).done($.proxy(function () {
                            self.reload();
                        }, this));
                    },

                    /**
                     * @return {Boolean}
                     */
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        /**
         * @param {Object} data
         */
        drawBreadcrumbs: function (data) {
            var node, breadcrumbs;

            if (this.element.find('#breadcrumbs').length) {
                this.element.find('#breadcrumbs').remove();
            }
            node = data.rslt.obj.data('node');

            if (node.id == 'root') { //eslint-disable-line eqeqeq
                return;
            }
            breadcrumbs = $('<ul class="breadcrumbs" id="breadcrumbs" />');
            $(data.rslt.obj.parents('[data-id]').get().reverse()).add(data.rslt.obj).each(function (index, element) {
                var nodeData = $(element).data('node');

                if (index > 0) {
                    breadcrumbs.append($('<li>\/</li>')); //eslint-disable-line
                }
                breadcrumbs.append($('<li />')
                    .data('node', nodeData).attr('data-row', 'breadcrumb').text(nodeData.text));

            });

            breadcrumbs.insertAfter(this.element.find('#content_header'));
        }
    });
	return $.custom.mbMediabrowser;
});
