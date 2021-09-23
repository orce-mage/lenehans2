/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */
var mtEditor = (function($) {

    var config = {
        log: 0,
        templateMaxWidth: 600,
        minWindowHeight: 600,
        data: {},
        fontFamilyOptions: {},
        template_id: 0
    };

    var removedBlockList = {};

    var init = function(options) {
        $.extend(config, options);

       if ($.cookie('mteditor_log') == '1') {
           config.log = 1;
        }

        initPopup();
        initEmailContent();
        initBlock();
        initBlockEvent();
        initVars();
        initCss();

        initLayout();
        initDragAndDrop();
        initEvent();
        initFileUpload();
        initPlaceholder();
        textEditHelper.init();
        loadImageList();
        saveHelper.init({
            template_id: config.template_id,
            action: {
                saveUrl: config.action.saveUrl
            },
            formKey: config.formKey
        });
        preloadImages();
    };


    var initEmailContent = function () {
        $('table[data-block-id]').each(function(){
            var blockHtml = $(this).clone().wrap("<span></span>").parent('span').html();
            $(this).replaceWith(wrapBlock(blockHtml, $(this).data('block-id')));
        });

        fixImageResponsive();
        prepareContent();
    };

    var prepareContent = function() {

       // $('.mteditor-contenteditable').attr('contenteditable', 'true').removeClass('mteditor-contenteditable');
        //if (config.contentHelper.length  == 0) {
       //     return;
      //  }
    };

    var fixImageResponsive = function() {
        $('#email img').each(function(){
            var elm = $(this);
            if (elm.css('max-width') == 'none') {
                elm.css('max-width', elm.width()+'px');
            }
        });
    };

    var initBlock = function() {
        if (config.template_id == 0) {
            initNewTemplate();
            return false;
        }
        $.each(config.data, function( index, value ) {
            $('#draggable').append('<table data-id="'+index+'"><tr><td><img width="250px" src="'+value.image+'"/></td></tr></table>');
            $('#hidden_content_block').append(wrapBlock(value.html, index));
        });
    };
    var initVars = function() {
        if (config.vars.length == 0) {
            return false;
        }
        $.each(config.vars, function( index, option ) {
            //exclude helpers vars
            var tmpLabel = option.label.split('_');
            if (tmpLabel[0] != 'helper') {
            $('select[name="editor_var"]').append($("<option></option>")
                .attr("value",option.value)
                .text(option.label));
            }
        });
    };


    var initCss = function() {
        if (config.data.length == 0) {
            return false;
        }

        if (config.body.css && config.body.css.length > 0) {
            $('#email_css').append(config.body.css);
        }

        $.each(config.data, function( index, option ) {
            if (option.css && option.css.length > 0) {
                $('#email_css').append(' '+ option.css);
            }
        });
    };

    var wrapBlock = function(blockHtml, index) {
        var content = '<table id="block_'+index+'" data-block="'+index+'" width="100%" border="0" cellpadding="0" cellspaceing="0"><tr><td>'+blockHtml+'</td></tr></table>';
        $('#mteditor_tmp').html(content);
        $('#mteditor_tmp *[data-block-content] td').first().prepend($('#block-action').clone().attr('class', 'block-action').removeAttr('id'));
        return $('#mteditor_tmp').html();
    };

    var initNewTemplate = function() {
        var validate = function() {
            var localeCode = $('#esns_box_layer select[name="email_locale"]').val();
            var templateCode = $('#esns_box_layer select[name="email_template"]').val();
            var name = $('#esns_box_layer input[name="template_code"]').val();
            var subject = $('#esns_box_layer input[name="template_subject"]').val();
            if (localeCode && templateCode && name && subject) {
                $('#esns_box_layer button[data-action="1"]').removeAttr('disabled');
                return true;
            } else {
                $('#esns_box_layer button[data-action="1"]').attr('disabled', 'disabled');
                return false;
            }
        };

        popup.content({
            contentSelector: '#init_new_template',
            disableClose: true
        }, function(){
            //textEditHelper.initLinkForm();
        }, function() {
            //textEditHelper.updateLink();
        }, function() {
            //textEditHelper.updateLink();
        });

        $('select.init-template').change(function(){
            var localeCode = $('#esns_box_layer select[name="email_locale"]').val();
            var templateCode = $('#esns_box_layer select[name="email_template"]').val();
            if (templateCode == '') {
                $('#esns_box_layer .form-group.hidden-template').hide();
                validate();
                return;
            }
            sendRequest(
                config.action.initTemplateUrl, {
                    localeCode: localeCode,
                    template: templateCode
                }, function(response) {
                    if (response.template.template_subject) {
                        $('#esns_box_layer input[name="template_subject"]').val(response.template.template_subject);
                    }
                    $('#esns_box_layer .form-group.hidden-template').show();
                    popup.eventResize();
                    validate();
                }
            );
        });

        $('select.get-template-collection').change(function(){
            var emailDesign = $('#esns_box_layer select[name="email_design"]').val();
            sendRequest(
                config.action.getTemplateCollectionUrl, {
                    design: emailDesign
                }, function(response) {
                    if (response.data) {
                        var selectElement = $('#esns_box_layer select[name="email_template"]');
                        selectElement.empty();
                        selectElement.append($("<option></option>")
                            .attr("value", '').text(' -- -- '));

                        $.each(response.data, function(key, value) {
                            selectElement.append($("<option></option>")
                                .attr("value", value.value).text(value.label));
                        });
                    }
                }
            );
        });

        setTimeout(function () {
            $('select.get-template-collection').trigger('change');
        }, 500);

        $('input[name="template_code"], input[name="template_subject"]').keyup(function(){
            validate();
        });

        $('#esns_box_layer button[data-action="0"]').click(function(){
            window.location = config.action.back;
        });

        $('#esns_box_layer button[data-action="1"]').click(function(){
            if (validate()) {
                var code = $('#esns_box_layer input[name="template_code"]').val();
                var localeCode = $('#esns_box_layer select[name="email_locale"]').val();
                var storeId = $('#esns_box_layer select[name="store_id"]').val();
                var subject = $('#esns_box_layer input[name="template_subject"]').val();
                var origTemplateCode = $('#esns_box_layer select[name="email_template"]').val();
                sendRequest(config.action.createTemplateUrl, {
                        template_code: code,
                        localeCode: localeCode,
                        store_id: storeId,
                        template_subject: subject,
                        template_text: '',
                        template_variables: '',
                        variables: '',
                        orig_template_code: origTemplateCode
                    }, function(response) {
                        if (response.success && response.success == 1) {
                            window.location = response.redirectTo;
                        } else if (response.error) {
                            $('#esns_box_layer .response-error').html(response.error);
                        }
                    }
                );
            }
        });
    };



    var initBlockEvent = function() {
        textEditHelper.init();
        $('table[data-block]').unbind('click').on('click', function(e) {

            $('.active-block').removeClass('active-block');
            $(this).addClass('active-block');

            var pos = $(this).find('*[data-block-content]').offset();
            $('.block-action').hide();
            $('.active-block .block-action').show();

            var blockActionPos = $('.active-block .block-action').offset();
            var mainContainerPos = $('#email').offset();
            if (blockActionPos.left < mainContainerPos.left) {
                $('.active-block .block-action').css('left', '20px');
            }

        }).unbind('mouseover').mouseover(function(e){
            var target = $( e.target );

            if(
                target.parents(".block-action").length==0
                && !target.hasClass("block-action")
            ) {
                $( "#email" ).sortable('disable');
            }
        });

        $('.block-action-move').unbind('mouseover').mouseover(function(){
            $( "#email" ).sortable('enable');
        }).mouseleave(function(){
            $( "#email" ).sortable('disable');
        });

        $('.block-action-delete').unbind('mouseover').mouseover(function(){
            $( "#email" ).sortable('disable');
        });

        $('#draggable').unbind('mouseover').mouseover(function(){
            $( "#email" ).sortable('enable');
        });

        $('.block-action-delete').unbind('click').click(function(){
            popup.confirm({
                'msg': 'Are you sure you want to delete this block?',
                'disableAutoClose': true
            }, function(){
                removeActiveBlock();
                popup.close();
            }, function(){
                popup.close();
            });
        });

        $('.block-action-source').unbind('click').click(function(){
            var elm = $('.active-block');
            if (elm.length == 0) {
                return;
            }

            popup.content({
                contentSelector: '#edit_html',
                disableClose: false
            }, function(){
                $('#esns_box_layer textarea[name="html"]').val($('.active-block td').first().html());
            }, function() {
                $('.active-block td').first().html($('#esns_box_layer textarea[name="html"]').val());
                initBlockEvent();
            }, function() {
            });
        });

        $('a[data-selector="edit-css"]').unbind('click').click(function(){
            popup.content({
                contentSelector: '#edit_css',
                disableClose: false
            }, function(){
                $('#esns_box_layer textarea[name="css"]').val($('#email_css').html());
            }, function() {
                $('#email_css').html($('#esns_box_layer textarea[name="css"]').val());
            }, function() {
            });
        });

        fixImageResponsive();
    };


    var initStyle = function() {
        if (!$('.active-block').length) {
            $('.empty-style-panel').show();
        } else {
            $('.empty-style-panel').hide();
        }

        initStyleColor('mteditor-bgcolor', 'mtedit_bgcolor', 'background-color');
        initStyleColor('mteditor-color', 'mtedit_color', 'color');
        initStyleAttr('mteditor-color', 'mtedit_font_size', 'font-size', 'input', {});
        initStyleAttr('mteditor-color', 'mtedit_font_family', 'font-family', 'select',  config.fontFamilyOptions);
        initColorPicker();
    };

    var initStyleColor = function(templateClass, listId, cssAttribute) {
        var addedClass = {};
        var ignoreClass = {
            'mteditor-content-helper-text' : '1',
            'mteditor-content-helper-link' : '1',
            'mteditor-content-helper-img' : '1',
            'mteditor-content-helper-selected' : '1',
            'editor-helper-active' : '1',
            'editor-selected-link' : '1'
        };

        $('#'+listId+' ul').html('');
        var counter = 0;
        $('.active-block .'+templateClass).each(function() {
            var classList = $(this).attr('class').split(/\s+/);

            var rgbColor = $(this).css(cssAttribute);
            if (rgbColor == 'transparent') {
                var color = '';
            } else {
                var color = toHex(rgbColor);
            }

            var cssColor = '#000000';

            $.each(classList, function(key, value) {
                if (value.length > 0 && value != templateClass && !addedClass[value] && !ignoreClass[value]) {
                    if (colorPicker.isDarkColor(rgbColor)) {
                        cssColor = '#ffffff';
                    }
                    $('#'+listId+' ul').append('<li><span>'+value+'</span> <input class="color" name="'+value+'" value="'+color+'" style="background-color: '+color+'; color: '+cssColor+';"></li>');
                    counter++;
                    addedClass[value] = 1;
                }
            });

        });

        if (counter == 0) {
            $('#'+listId).hide();
            return;
        }
        $('#'+listId).show();

        $('#'+listId+' input').on('change', function(){
            var className = $(this).attr('name');

            if (canApplyToAll()) {
                $('#email .'+className).css(cssAttribute, $(this).val());
                if (listId == 'mtedit_bgcolor') {
                    $('#email table.'+className+', #email table tr.'+className+', #email table tr td.'+className+', #email *[bgcolor].'+className).attr('bgcolor', $(this).val());
                }
            } else {
                $('#email .active-block .'+className).css(cssAttribute, $(this).val());
                if (listId == 'mtedit_bgcolor') {
                    $('#email .active-block table.'+className+', #email .active-block table tr.'+className+', #email .active-block table tr td.'+className+', #email .active-block *[bgcolor].'+className).attr('bgcolor', $(this).val());
                }
            }
        });
    };

    var initStyleAttr = function(templateClass, listId, cssAttribute, inputType, options) {
        var addedClass = {};
        var ignoreClass = {
            'mteditor-content-helper-text' : '1',
            'mteditor-content-helper-link' : '1',
            'mteditor-content-helper-img' : '1',
            'mteditor-content-helper-selected' : '1',
            'editor-helper-active' : '1',
            'editor-selected-link' : '1'
        };

        $('#'+listId+' ul').html('');
        var counter = 0;
        $('.active-block .'+templateClass).each(function() {
            var classList = $(this).attr('class').split(/\s+/);
            var attributeValue = $(this).css(cssAttribute);
            $.each(classList, function(key, value) {
                if (value.length > 0 && value != templateClass && !addedClass[value] && !ignoreClass[value]) {
                    var inputHtml = '';
                    if (inputType == 'input') {
                        inputHtml = '<input class="'+cssAttribute+'" name="'+value+'" value="'+attributeValue+'">';
                    }

                    if (inputType == 'select') {
                        //attributeValue
                        inputHtml = '<select class="'+cssAttribute+'" name="'+value+'">';
                        $.each(options, function(key, value) {
                            var selected = '';

                            if (cssAttribute == 'font-family' && mtEditor.isSameFonts(value, attributeValue)) {
                                selected = 'selected="selected"';
                            } else if (value == attributeValue) {
                                selected = 'selected="selected"';
                            }
                            inputHtml = inputHtml +'<option '+selected+'>'+value+'</option>';
                        });
                        inputHtml = inputHtml +'</select>';
                    }


                    $('#'+listId+' ul').append('<li><span>'+value+'</span> '+inputHtml+'</li>');
                    counter++;
                    addedClass[value] = 1;
                }
            });

        });

        if (counter == 0) {
            $('#'+listId).hide();
            return;
        }

        $('#'+listId).show();

        $('#'+listId+' '+inputType).on('change', function(){
            var className = $(this).attr('name');
            var value = $(this).val();

            if (cssAttribute == 'font-family') {
                changeFont(value, className);
            } else {
                if (canApplyToAll()) {
                    $('#email .'+className).css(cssAttribute, value);
                } else {
                    $('#email .active-block .'+className).css(cssAttribute, value);
                }
            }
        });
    };
    
    var isSameFonts = function (fontA, fontB) {
        var fontATmp = fontA.split('"').join('').split('"').join('').split(' ').join('').split(',');
        var fontBTmp = fontB.split('"').join('').split('"').join('').split(' ').join('').split(',');

        for (var i = 0; i < fontATmp.length; i++) {
            if (String(fontATmp[i]).indexOf(String(fontBTmp[i])) == -1) {
                return false;
            }
        }

        return true;
    };

    var changeFont = function (value, className) {
        var fontFamily = value.split('"').join('\'');
        if (canApplyToAll()) {
            var elements = $('#email .'+className);
        } else {
            var elements = $('#email .active-block .'+className);
        }

        elements.each(function () {
            var element = $(this);
            element.css('font-family', 'FONTFAMILY');
            var style = element.attr('style').replace('FONTFAMILY', fontFamily);
            element.attr('style', style);
        });
    }

    var loadImageList = function() {
        $.each(config.imageList, function(key, value){
            $('.mteditor-image-list').prepend('<li><img src="'+value+'"/></li>');
        });
    };

    var initImage = function() {
        log('init image');
        var activeImg = $('.'+textEditHelper.config.classes.helperImg);
        var contentEditable = $('.'+textEditHelper.config.classes.helperText);

        var emptyPanel = $('.empty-image-panel');
        if (!activeImg.length && !contentEditable.length) {
            emptyPanel.show();
            return;
        }

        $('.mteditor_upload_new').show();
        emptyPanel.hide();

        var imgWidth = activeImg.css('width');
        var imgHeight = activeImg.css('height');

        var imgStyle = activeImg.prop('style');

        if (imgStyle && imgStyle.getPropertyValue('width')) {
            imgWidth = imgStyle.width;
        }

        if (imgStyle && imgStyle.getPropertyValue('height')) {
            imgHeight = imgStyle.height;
        }

        $('#image-width').val(imgWidth);
        $('#image-height').val(imgHeight);
        $('#image-alt').val(activeImg.attr('alt'));

        initImageEvent();
    };

    var initImageEvent = function() {
        $('.mteditor-image-list li').unbind('click').click(function() {
            log('click on image');
            var selectedImage = $('.'+textEditHelper.config.classes.helperImg);
            var contentEditable = $('.'+textEditHelper.config.classes.helperText);

            if (selectedImage.length == 0 && contentEditable.length == 0) {
                return;
            }

            var image = null;
            if (selectedImage.length > 0) {
                image = selectedImage;
            } else {
                image = $('<img />');
            }

            var width = $('#image-width').val();
            var height = $('#image-height').val();
            var alt = $('#image-alt').val();
            var border = $('#image-border').val();
            var margin = $('#image-margin').val();
            var src = $(this).find('img').attr('src');

            image.attr('src', src);
            if (width.length) {
                image.css('width', width).css('max-width', width);
            }

            if (height.length) {
                image.css('height', height);
            }

            if (border.length) {
                image.css('border', border);
            }

            if (margin.length) {
                image.css('margin', margin);
            }

            if (alt.length) {
                image.attr('alt', alt);
            }

            if (contentEditable.length > 0) {
                image.addClass('atr');
                $('.'+textEditHelper.config.classes.helperContentImage).replaceWith(image);
                image.trigger('click');
            }
        });

        $('#image-width, #image-height, #image-alt, #image-margin, #image-border').unbind('keyup').keyup(function(){
            updateSelectedImageSize();
        });
    };

    var updateSelectedImageSize = function() {
        var imageWidth = $('#image-width').val();
        var imageHeight = $('#image-height').val();
        $('.'+textEditHelper.config.classes.helperImg).css({
            'width': imageWidth,
            'border': $('#image-border').val(),
            'margin': $('#image-margin').val(),
            'height': imageHeight,
            'max-width': imageWidth
        }).attr('alt', $('#image-alt').val()).attr('width', imageWidth.replace("px", "")).attr('height', imageHeight.replace("px", ""));
    };



    var initLayout = function() {
        reloadSizes();
        $('#main-menu').metisMenu();

    };

    var reloadSizes = function() {
        var windowHeight = $(window).height();
        if (config.minWindowHeight > windowHeight) {
            windowHeight = config.minWindowHeight;
        }

        $('#editor_wrapper').height(windowHeight+'px');
        $('.sidebar').height(windowHeight+'px');
        $('#page-wrapper').height(windowHeight+'px');
        $('#email_body').css('max-width', config.templateMaxWidth+'px');
        $('.tools').height(windowHeight+'px');
    };

    var initDragAndDrop = function() {

        $("#draggable table").draggable({
            connectToSortable: "#email",
            helper: "clone",
            revert: "invalid",
            zIndex: 11
        });

        $( "#email" ).sortable({
            revert: false,
            items: '> table',
            distance: -40,
            zIndex: 10,
            placeholder: {
                element: function(currentItem) {
                    return $('<table class="ui-sortable-placeholder">' + '<tr><td></td></tr></table>')[0];
                },
                update: function(container, p) {
                    return;
                }
            },

            start: function (event, ui) {
                textEditHelper.hide();
            },

            beforeStop: function (event, ui) {
                if (!ui.item.data('id')) {
                    return;
                }
                ui.item.replaceWith($('#block_placeholder').html());
            },

            stop: function (event, ui) {
                $('#email .empty-placeholder').remove();
            },

            over: function() {
                $('#email .empty-placeholder').hide();
            },

            out: function() {
                $('#email .empty-placeholder').show();
            },

            update: function(event, ui) {
                if (!ui.item.data('id')) {
                    return;
                }
                //var item = $('#block_'+ui.item.data('id')).clone();

                sendRequest(config.action.createNewBlock, {
                    template_id: config.template_id,
                    content: config.data[ui.item.data('id')]['content']
                }, function(response) {
                    if (response.success == 1) {
                        var newBlockId = response.newBlockId;
                        var newBlockContent = wrapBlock(response.block, newBlockId);
                        var item = $(newBlockContent);
                        $('#email .block-placeholder').replaceWith(item.hide().fadeIn({duration: 1000}));
                        prepareContent();
                        initBlockEvent();

                    } else {
                        mtEditor.log(response.error);
                    }
                });
            }
        });
    };

    var initEvent = function() {
        log('initEvent');
        $( "a" ).click(function( event ) {
            event.preventDefault();
        });

        $(window).resize(function(){
            reloadSizes();
        });

        $('a[data-selector="edit-layout"]').unbind('click').click(function(){
            openLayoutTools();
        });

        $('a[data-selector="edit-style"]').unbind('click').click(function(){
            openStyleTools();
        });

        $('a[data-selector="edit-image"]').unbind('click').click(function(){
            openImageTools();
        });


        $('.nav li a').click(function(){
            $('.nav li a').removeClass('active');
            $(this).addClass('active');
        });

        $('#email .block').click(function(){
            $('a.open-tools[data-selector="edit-layout"]').trigger('click');
        });

        $('#switch').click(function() {
            if ($(this).hasClass('inactive')) {
                $('#switch_thumb').switchClass("inactive", "active", 100, "linear");
                $('#switch').switchClass("inactive", "active", 100, "linear");
            } else {
                $('#switch_thumb').switchClass("active", "inactive", 100, "linear");
                $('#switch').switchClass("active", "inactive", 100, "linear");
            }
        });

        $('#switch_auto_save').click(function() {
            if ($(this).hasClass('inactive')) {
                $('#switch_thumb_auto_save').switchClass("inactive", "active", 100, "linear");
                $('#switch_auto_save').switchClass("inactive", "active", 100, "linear");
                saveHelper.startAutoSave();
            } else {
                $('#switch_thumb_auto_save').switchClass("active", "inactive", 100, "linear");
                $('#switch_auto_save').switchClass("active", "inactive", 100, "linear");
                saveHelper.stopAutoSave();
            }
        });

        $('#switch_apply_to_all').click(function() {
            if ($(this).hasClass('inactive')) {
                $('#switch_thumb_apply_to_all').switchClass("inactive", "active", 100, "linear");
                $('#switch_apply_to_all').switchClass("inactive", "active", 100, "linear");

            } else {
                $('#switch_thumb_apply_to_all').switchClass("active", "inactive", 100, "linear");
                $('#switch_apply_to_all').switchClass("active", "inactive", 100, "linear");

            }
        });

        $('a[data-action="preview-full-screen"]').click(function(){
            var previewUrl = '';
            getPreviewLink(function(response) {
                if (response.success == 1) {
                    window.open(config.action.previewUrl, '_blank');
                }
            });
        });

        $('button[data-action="back"]').click(function(){
            popup.confirm({
                'msg': 'Do you want to save the changes?',
                'disableAutoClose': true
            }, function(){
                $('#esns_box_layer a[data-action="1"]').text('Saving...');
                saveHelper.save(function(response){
                    window.location = config.action.back;
                });
            }, function(){
                window.location = config.action.back;
            });
        });

        $('a[data-action="preview-mobile"]').click(function(){
            var previewUrl = '';
            getPreviewLink(function(response) {
                if (response.success == 1) {
                    popup.content({
                        contentSelector: '#mobile_preview',
                        disableClose: false
                    }, function(){
                        $('#esns_box_layer iframe').attr('src', config.action.previewUrl);
                    }, function() {
                    }, function() {
                    });

                }
            });
        });


        $('a[data-action="send-email"]').click(function(){
            var previewUrl = '';

            popup.content({
                contentSelector: '#send_test_message',
                disableClose: false
            }, function(){

                $('input[name="send_email[email]"]').unbind('keyup').keyup(function(){
                    var email = $(this).val();
                    var actionButton = $('#esns_box_content button[data-action="1"]');
                    if (!isEmailValid(email)) {
                        actionButton.attr('disabled', 'disabled');
                        return;
                    }
                    actionButton.removeAttr('disabled');
                });

                var lastEmail = $.cookie('last_test_email');
                if (lastEmail && isEmailValid(lastEmail)) {
                    $('#esns_box_content input[name="send_email[email]"]').val(lastEmail);
                    $('#esns_box_content button[data-action="1"]').removeAttr('disabled');
                }
                $('#esns_box_content .response-error').hide();
                $('#esns_box_content .response-success').hide();

                $('button[data-action="1"]').unbind('click').click(function(){
                    var email = $('#esns_box_content input[name="send_email[email]"]').val();
                    if (isEmailValid(email)) {
                        $.cookie('last_test_email', email, { expires: 199, path: '/' });
                        var button = $(this);
                        button.text('Sending...');
                        $('#esns_box_content .response-error').hide();
                        $('#esns_box_content .response-success').hide();

                        sendRequest(config.action.sendTestEmilUrl, {
                            content: JSON.stringify(saveHelper.getPreparedContent()),
                            vars: JSON.stringify(saveHelper.getContentVars()),
                            css: JSON.stringify(saveHelper.getCss()),
                            id: config.template_id,
                            email: email
                        }, function(response) {
                            if (response.success == 1) {
                                $('#esns_box_content .response-success').text('Email has been sent successful.').show();

                            } else {
                                $('#esns_box_content .response-error').text(response.error).show();
                            }
                            button.text('Send');
                        });
                    }
                });

                $('#esns_box_content a[data-action="0"]').click(function(e){
                    popup.close(true);
                });
            }, function() {



            }, function() {

            });

        });

        $('a[data-action="change-info"]').click(function(){
            popup.content({
                contentSelector: '#change_info',
                disableClose: false,
                disableCloseAfterSubmit: true
            }, function(){
                $('#esns_box_content .response-error').hide();
                $('#esns_box_content .response-success').hide();
                $('#esns_box_content input[name="template_code"]').val(config.template.code);
                $('#esns_box_content input[name="template_subject"]').val(config.template.subject);
                $('#esns_box_content select[name="store_id"]').val(config.template.store_id);
            }, function(){
                $('#esns_box_layer a[data-action="1"]').text('Saving...');
                var newTemplateCode = $('#esns_box_content input[name="template_code"]').val();
                var newTemplateSubject = $('#esns_box_content input[name="template_subject"]').val();
                var newStoreId = $('#esns_box_content select[name="store_id"]').val();
                sendRequest(config.action.saveInfo, {
                        template_code: newTemplateCode,
                        template_subject:  newTemplateSubject,
                        store_id:  newStoreId,
                        id: config.template_id
                    }, function(response) {
                        if (response.success == 1) {
                            config.template.code = newTemplateCode;
                            config.template.subject = newTemplateSubject;
                            config.template.store_id = newStoreId;
                            $('#esns_box_content .response-error').hide();
                            $('#esns_box_content .response-success').text('Template has been saved successful!').show();
                            $('#esns_box_layer a[data-action="1"]').text('Save');
                            setTimeout(function(){
                                popup.config.disableClose = false;
                                popup.close(true);
                            }, 2000);
                        } else if (response.error.length > 0) {
                            $('#esns_box_content .response-error').text(response.error).show();
                            $('#esns_box_content .response-success').hide();
                        }

                    }
                );
            }, function(){
                popup.close();
            });
        });

        $('a[data-action="delete-template"]').click(function(){
            popup.confirm({
                'msg': 'Are you sure? Do You want to delete this template?',
                'disableAutoClose': true
            }, function(){
                $('#esns_box_layer a[data-action="1"]').text('Deleting...');
                sendRequest(config.action.deleteTemplateAjax, {
                        id: config.template_id
                    }, function(response) {
                        if (response.success == 1) {
                            window.location = config.action.back;
                        } else if (response.error.length > 0) {

                        }
                    }
                );
            }, function(){
                popup.close(true);
            });
        });
    };
    var openLayoutTools = function() {
        beforeOpenLayoutTools();
        openTools('edit-layout');
    };

    var openImageTools = function() {
        beforeOpenImageTools();
        openTools('edit-image');
    };

    var openStyleTools = function() {
        beforeOpenStyleTools();
        openTools('edit-style');
    };


    var openTools = function(className) {
        var openPanel = '.tools.' + className;
        if ($(openPanel).hasClass('active')) {
            return false;
        }
        $('.nav a[data-selector]').removeClass('active');
        $('.nav a[data-selector="'+className+'"]').addClass('active');
        $( '.tools').css('z-index', 3);
        $(openPanel).css('z-index', 4);
        $( '.tools.active' ).animate({
            left: '-108'
        }, 200, function() {
            $(openPanel).animate({
                left: '200'
            }, 200).addClass('active');
        }).removeClass('active');
    };

    var isEmailValid = function(value) {
        if ( value.length >= 6 && value.split('.').length > 1 && value.split('@').length == 2) {
            return true;
        }
        return false;
    };

    var getPreviewLink = function(callback) {
        showLoading();
        var content = saveHelper.getPreparedContent();
        var vars = saveHelper.getContentVars();
        var css = saveHelper.getCss();
        sendRequest(config.action.preparePreviewAjaxUrl, {
                content: JSON.stringify(content),
                vars: JSON.stringify(vars),
                css: JSON.stringify(css),
                id: config.template_id
            }, function(response) {
                hideLoading();
                callback(response);
            }
        );
    };


    var reloadEditorEvents = function() {

    };

    var initFileUpload = function() {
        $('#imageupload').fileupload({
            singleFileUploads: true,
            url: config.action.uploadUrl+'?isAjax=1',
            formData: {form_key: config.formKey},
            dropZone: undefined
        }).bind('fileuploadchange', function (e, data) {
            $('#imageupload .mteditor_upload_button').text('Uploading....');
            $('#imageupload input[type="file"]').attr('disabled', 'disabled');
            $('#imageupload .fileupload-buttonbar i.glyphicon').removeClass('glyphicon-plus').addClass('glyphicon-upload');
        }).bind('fileuploaddone', function (e, data) {
            $('#imageupload .mteditor_upload_button').text('Select image');
            $('#imageupload input[type="file"]').removeAttr('disabled');
            $('#imageupload .fileupload-buttonbar i.glyphicon').addClass('glyphicon-plus').removeClass('glyphicon-upload');

            var result = data.result;
            if (result.success == 1) {
                $('.mteditor-image-list').prepend('<li><img src="'+result.fileUrl+'"/></li>');
                initImage();
            }
        });
    };

    var removeActiveBlock = function() {
        var blockId = $('.active-block').data('block');
        $('.active-block').remove();
        mtEditor.removedBlockList[blockId] = 1;

        initStyle();
        initPlaceholder();
    };

    var initPlaceholder = function() {
        if ($('#email table').length == 0) {
            $('#email').append($('#empty_placeholder').clone().html());
            $('#email .empty-placeholder').show();
            $('#email').sortable('enable');
            $('a[data-selector="edit-layout"]').trigger('click');
        }
    };

    var canApplyToAll = function() {
        return $('#switch').hasClass('active');
    };

    var initColorPicker = function() {
        colorPicker.init();
    };

    var toHex = function(rgb) {
        var hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");

        function hex(x) {
            return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
        }

        rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
    };

    var initPopup = function(){
        popup.init();
    };

    var sendRequest = function(url, data, callBack) {
            data.form_key = config.formKey;
            $.ajax({
            url: url+'?isAjax=1',
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(response) {
                callBack(response);
            }
        });
    };

    var beforeOpenImageTools = function() {
        log('beforeOpen Image Tools');
        initImage();
    };

    var beforeOpenLayoutTools = function() {
        log('beforeOpen Layout Tools');
        $('#email').sortable('enable');
    };

    var beforeOpenStyleTools = function() {
        log('beforeOpen Style Tools');
        initStyle();
    };

    var log = function(msg) {
        if (config.log == 1) {
            console.log(msg);
        }
    };

    var preloadImages = function() {
        $('.mteditor-image-list img').each(function(){
            $("<img />").attr("src", $(this).attr('src'));
        })
    };

    var showLoading = function() {
        popup.content({
            contentSelector: '#loading',
            disableClose: true,
            disableCloseAfterSubmit: true
        }, function(){}, function(){});
    };

    var hideLoading = function()
    {
        popup.close(true);
    };

    return {
        init: init,
        config: config,
        log: log,
        initBlockEvent: initBlockEvent,
        initImage: initImage,
        openStyleTools: openStyleTools,
        openImageTools: openImageTools,
        reloadEditorEvents: reloadEditorEvents,
        removedBlockList: removedBlockList,
        toHex: toHex,
        isSameFonts: isSameFonts
    };

})(jQuery);


function requirejs($a, $b) {
    return;
}

function require($a, $b) {
    return;
}