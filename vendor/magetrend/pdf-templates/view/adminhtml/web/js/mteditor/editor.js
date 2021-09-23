/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
var mtEditor = (function($) {

    var ppi = 96;

    var a4WidthInInches = 8.3;

    var a4HeightInInches = 11.7;

    var paperWidthInPixels = 0;

    var paperHeightInPixels = 0;

    var config = {
        log: 0,
        templateMaxWidth: 600,
        minWindowHeight: 600,
        data: {},
        fontFamilyOptions: {},
        template_id: 0,
        elements:{},
        paperWidth: 595,
        paperHeight: 842,
        additioanlPage: {}
    };

    var removedBlockList = {};

    var init = function(options) {
        $.extend(config, options);

        if ($.cookie('mteditor_log') == '1') {
            config.log = 1;
        }
        initFonts();
        initPaper();

        pdfElement.init(config);
        initPopup();
        initEmailContent();
        initBlock();
        initBlockEvent();

        initLayout();
        initDragAndDrop();
        initEvent();
        initFileUpload();
        initPlaceholder();
        textEditHelper.init();

        loadImageList();
        initSettingsEvent();
        initTemplateOptions();
        saveHelper.init({
            template_id: config.template_id,
            action: {
                saveUrl: config.action.saveUrl
            },
            formKey: config.formKey
        });
        preloadImages();
    };

    var initPaper = function () {
        paperWidthInPixels = Math.ceil(config.paperWidth * ppi / 72);
        paperHeightInPixels = Math.ceil(config.paperHeight * ppi / 72);

        $('.paperA4').css({
            width: paperWidthInPixels+'px',
            height: paperHeightInPixels+'px'
        });
    };

    var setPPI = function () {
        $('body').append($('<div id="in" style="width: 1in"></div>'));
        ppi = $('#in').width();
        $('#in').remove();
    };


    var initEmailContent = function () {
        $('table[data-block-id]').each(function(){
            var blockHtml = $(this).clone().wrap("<span></span>").parent('span').html();
            $(this).replaceWith(wrapBlock(blockHtml, $(this).data('block-id')));
        });

        fixImageResponsive();
        prepareContent();
    };

    var prepareContent = function() {};

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
        $.each(config.elements, function( index, value ) {
            if (index == 'element_image') {
                return;
            }
            $('#draggable').append(
                '<a  data-id="'+index+'" class="pdfe" href="javascript:void(0)">'+
                    '<span class="'+value.config.icon+'"></span>'+
                    '<span class="pdfe-text">'+value.config.label+'</span>'+
                '</a>'
            );
            $('#hidden_content_block').append(wrapBlock(value.html, index));
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
            var name = $('#esns_box_layer input[name="template_name"]').val();
            if (name) {
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

        $('input[name="template_name"]').keyup(function(){
            validate();
        });

        $('select[name="template_design"], input[name="template_subject"]').change(function(){
            validate();
        });

        $('select[name="template_size"]').change(function(){
            var templateType = $('#esns_box_layer input[name="template_type"]').val();
            $('#esns_box_layer button[data-action="1"]').attr('disabled', 'disabled');
            sendRequest(config.action.designList, {
                    size: $(this).val(),
                    template_type: templateType,
                }, function(response) {
                    if (response.success && response.success == 1) {
                        var options = '';
                        $('select[name="template_design"]').empty().append($('<option value="">Empty</option>'));
                        if (response.data) {
                            $.each(response.data, function (value, label) {
                                $('select[name="template_design"]')
                                    .append($("<option></option>")
                                        .attr("value", value)
                                        .text(label));
                            });
                        }
                    } else if (response.error) {
                        $('#esns_box_layer .response-error').html(response.error);
                    }

                    $('#esns_box_layer button[data-action="1"]').removeAttr('disabled');
                }
            );
        });


        $('#esns_box_layer button[data-action="0"]').click(function(){
            window.location = config.action.back;
        });

        $('#esns_box_layer button[data-action="1"]').click(function(){
            if (validate()) {
                var design = $('#esns_box_layer select[name="template_design"]').val();
                var templateType = $('#esns_box_layer input[name="template_type"]').val();
                var name = $('#esns_box_layer input[name="template_name"]').val();
                var localeCode = $('#esns_box_layer select[name="template_locale"]').val();
                var templateSize = $('#esns_box_layer select[name="template_size"]').val();
                var storeId = $('#esns_box_layer select[name="template_store_id"]').val();
                sendRequest(config.action.createTemplateUrl, {
                        template_name: name,
                        template_locale: localeCode,
                        template_store_id: storeId,
                        template_design: design,
                        template_type: templateType,
                        template_size: templateSize,
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

        }).unbind('mouseover').mouseover(function(e){
            var target = $( e.target );

            if(
                target.parents(".block-action").length==0
                && !target.hasClass("block-action")
            ) {
                $( "#email" ).sortable('disable');
            }
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

    var initProperties = function() {
        if (!$('.pdf-element.active').length) {
            $('.empty-style-panel').show();
            return;
        } else {
            $('.empty-style-panel').hide();
        }

        $('.edit-properties .tools-container ul li').each(function () {
            if ($(this).attr('id')) {
                initPropertiesBlock($(this).attr('id'));
            }
        });

        initContentEditable();
        initColorPicker();
    };

    var initColorScheme = function () {

        initColorSchemeBlock('bg');
        initColorSchemeBlock('font');
        initColorSchemeBlock('border');
        initColorPicker();
    };

    var initColorSchemeBlock = function(group) {
        var colorConfig = {};
        var i = 0;
        $.each(config.color[group], function (key, options) {

            $(options['className']).each(function () {

                if ($(this).hasClass('pdf-element')) {
                    var elementId = $(this).attr('id');
                } else {
                    var elementId = $(this).closest('.pdf-element').attr('id');
                }

                if (elementId == undefined || elementId == '') {
                     return;
                }

                if (options['onLoad']) {
                    var currentValue = eval(options.onLoad+'(\''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\');');
                    var colorIndex = colorToIndex(currentValue);
                    if (!colorConfig[colorIndex]) {
                        colorConfig[colorIndex] = {
                            value : currentValue,
                            options : options,
                            elements: {}
                        };

                    }
                    colorConfig[colorIndex]['elements'][i] = {
                        elementId : elementId,
                        options : options
                    };
                    i++;
                }
            });
        });

        var listElement = $('#color_scheme_'+group);
        listElement.find('ul').html('');
        var i = 1;
        $.each(colorConfig, function (id, color) {
            if (id == 'rgba') {
                return;
            }
            listElement.find('ul').append(getColorSchemeInput(color.value, color.elements, i));
            i++;
        });


    };

    var getColorSchemeInput = function (currentValue, elements, index) {
        var elementName = '';
        var onChange = ' onchange=" ';
        $.each(elements, function (i, o) {
            var elementId = o.elementId;
            var options = o.options;
            onChange = onChange+options.onChange+'(this.value, \''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\');';
        });
        onChange = onChange +'" ';

        var rgbColor = currentValue;
        if (!rgbColor || rgbColor == 'rgba(0, 0, 0, 0)') {
            var color = '';
            currentValue = '';
        } else {
            var color = toHex(rgbColor);
        }

        var cssColor = '#000000';
        if (colorPicker.isDarkColor(rgbColor)) {
            cssColor = '#ffffff';
        }

        var elementHtml = '<li>'
            +'<span>Color #'+index+': </span>'
            +'<input '+onChange+' class="color" name="color_scheme_'+index+'" '
            +'value="'+toHex(currentValue)+'" style="background-color: '+color+'; color: '+cssColor+';"/>'
            +'</li>';

        return elementHtml;
    };

    var colorToIndex = function (color) {
        if (color == undefined || color.substr(0, 4) == 'rgba') {
            return 'rgba';
        }

        var tmp = color.split(' ').join('').replace('rgb(', '').replace(')', '').split(',');
        return tmp[0]+'_'+tmp[1]+'_'+tmp[2];

    };

    var initPropertiesBlock = function(listId) {
        var group = listId.replace('pdf_element_', '');
        var listElement = $('#'+listId);
        var element = $('.pdf-element.active');
        var elementType = element.data('element-type');
        var elementid = element.attr('id');
        listElement.find('ul').html('');

        if (config.elements[elementType]['config']['attributes']) {
            var attributes = config.elements[elementType]['config']['attributes'];

            $.each(attributes, function (elementName, options) {
                if (options.group != group) {
                    return;
                }
                listElement.find('ul').append(getPropertiesElement(elementid, elementName, options));
            });
        }
        if (listElement.find('ul li').length) {
            listElement.show();
        } else {
            listElement.hide();
        }
    }

    var getPropertiesElement = function (elementId, elementName, options) {
        if (!options['input']) {
            return '';
        }

        switch (options.input) {
            case 'text' :
                return getPropertiesElementText(elementId, elementName, options);
            case 'textarea' :
                return getPropertiesElementTextarea(elementId, elementName, options);
            case 'color' :
                return getPropertiesElementColor(elementId, elementName, options);
            case 'select' :
                return getPropertiesElementSelect(elementId, elementName, options);
            case 'checkbox' :
                return getPropertiesElementCheckbox(elementId, elementName, options);
        }
        return '';
    };

    var getPropertiesElementCheckbox = function (elementId, elementName, options) {
        var onChange = '';
        var currentValue = '';
        var checked = '';

        if (options['onChange']) {
            onChange = ' onchange="'+options.onChange+'(this.checked, \''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\')" ';
        }

        if (options['onLoad']) {
            currentValue = eval(options.onLoad+'(\''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\');');
        }

        if (currentValue == 'true' || currentValue == 1) {
            checked = ' checked="checked" '
        }

        var elementHtml = '<li class="pdf_option_checkbox">'
            +'<input type="checkbox" '+onChange+' '+checked+' name="'+elementName+'"  value="1"/> '
            +'<span>'+options.label+'</span>'
            +'</li>';

        return elementHtml;
    };

    var getPropertiesElementSelect = function (elementId, elementName, options) {
        var onChange = '';
        if (options['onChange']) {
            onChange = ' onchange="'+options.onChange+'(this.value, \''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\')" ';
        }

        var currentValue = '';
        if (options['onLoad']) {
            currentValue = eval(options.onLoad+'(\''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\');');
        }

        if (options['onLoadOptions']) {
            options.options = eval(options.onLoadOptions+'(\''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\');');
        }

        var elementHtml = '<li><span>'+options.label+'</span> <select name="'+elementName+'" '+onChange+' >'
                        + '<option value=""> -- --</option>';
        $.each(options.options, function (key, label) {
            var selected = '';
            if (currentValue == key) {
                selected = ' selected="selected" ';
            }
            elementHtml += '<option '+selected+' value="'+key+'">'+label+'</option>';
        });
        elementHtml += '</select></li>'
        return elementHtml;
    };

    var getPropertiesElementText = function (elementId, elementName, options) {
        var onChange = '';
        if (options['onChange']) {
            onChange = ' onchange="'+options.onChange+'(this.value, \''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\')" ';
        }

        var currentValue = '';
        if (options['onLoad']) {
            currentValue = eval(options.onLoad+'(\''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\');');
        }

        var elementHtml = '<li>'
            +'<span>'+options.label+'</span>'
            +'<input '+onChange+' name="'+elementName+'"  value="'+currentValue+'">'
            +'</li>';

        return elementHtml;
    };

    var getPropertiesElementTextarea = function (elementId, elementName, options) {
        var onChange = '';
        if (options['onChange']) {
            onChange = ' onchange="'+options.onChange+'(this.value, \''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\')" ';
        }

        var currentValue = '';
        if (options['onLoad']) {
            currentValue = eval(options.onLoad+'(\''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\');');
        }

        var elementHtml = '<li>'
            +'<span>'+options.label+'</span>'
            +'<textarea '+onChange+' name="'+elementName+'">'+currentValue+'</textarea>'
            +'</li>';

        return elementHtml;
    };

    var getPropertiesElementColor = function (elementId, elementName, options) {
        var onChange = '';
        if (options['onChange']) {
            onChange = ' onchange="'+options.onChange+'(this.value, \''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\')" ';
        }

        var currentValue = '';
        if (options['onLoad']) {
            currentValue = eval(options.onLoad+'(\''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\');');
        }

        var rgbColor = currentValue;
        if (!rgbColor || rgbColor == 'rgba(0, 0, 0, 0)') {
            var color = '';
            currentValue = '';
        } else {
            var color = toHex(rgbColor);
        }


        var cssColor = '#000000';
        if (colorPicker.isDarkColor(rgbColor)) {
            cssColor = '#ffffff';
        }

        var elementHtml = '<li>'
            +'<span>'+options.label+'</span>'
            +'<input '+onChange+' class="color" name="'+elementName+'" '
            +'value="'+toHex(currentValue)+'" style="background-color: '+color+'; color: '+cssColor+';"/>'
            +'</li>';

        return elementHtml;
    };





    var initContentEditable = function() {
        var element = $('.pdf-element.active');
        var elementType = element.data('element-type');
        var elementid = element.attr('id');

        if (config.elements[elementType]['config']['attributes']) {
            var attributes = config.elements[elementType]['config']['attributes'];

            $.each(attributes, function (elementName, options) {
                if (options.group != 'contenteditable') {
                    return;
                }
                var onChange = '';
                var currentValue = '';

                /*if (options['onLoad']) {
                    currentValue = eval(options.onLoad+'(\''+elementid+'\', \''+options.className+'\', \''+options.attribute+'\');');
                }*/

                $('#'+elementid+ ' '+options.className).attr('contenteditable', 'true');

            });
        }
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
                    $('#email table.'+className+', #email table tr.'+className+', #email table tr td.'+className+'').attr('bgcolor', $(this).val());
                }
            } else {
                $('#email .active-block .'+className).css(cssAttribute, $(this).val());
                if (listId == 'mtedit_bgcolor') {
                    $('#email .active-block table.'+className+', #email .active-block table tr.'+className+', #email .active-block table tr td.'+className+'').attr('bgcolor', $(this).val());
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
                            if (value == attributeValue) {
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
            if (canApplyToAll()) {
                $('#email .'+className).css(cssAttribute, $(this).val());
            } else {
                $('#email .active-block .'+className).css(cssAttribute, $(this).val());
            }
        });
    };



    var loadImageList = function() {
        $.each(config.imageList, function(key, value){
            $('.mteditor-image-list').prepend('<li><img src="'+value+'"/></li>');
        });
    };

    var initImage = function() {
        log('init image');
        var activeImg = $('.'+textEditHelper.config.classes.helperImg);
        var contentEditable = $('.'+textEditHelper.config.classes.helperText);

        initImageEvent();


        if (!activeImg.length && !contentEditable.length) {
            return;
        }

        $('.mteditor_upload_new').show();
        $('#image-width').val(activeImg.css('width'));
        $('#image-height').val(activeImg.css('height'));
        $('#image-alt').val(activeImg.attr('alt'));


    };

    var initSettings = function() {
        var footer = $('#pdf_footer');
        var header = $('#pdf_header');

        $('input[name="header_height"]').val(header.css('height'));
        $('input[name="footer_height"]').val(footer.css('height'));

        initSettingsEvent();
    };
    var initSettingsEvent = function() {

        if ($('.paperA4').css('overflow') == 'hidden') {
            $('input[name="hide_overflow"]').attr('checked', 'checked');
        } else {
            $('input[name="hide_overflow"]').removeAttr('checked');
        }

        $('input[name="hide_overflow"]').click(function () {
            if ($(this).is(':checked')) {
                $('.paperA4').css('overflow', 'hidden');
            } else {
                $('.paperA4').css('overflow', 'visible');
            }
        });

        var footer = $('#pdf_footer');
        var header = $('#pdf_header');

        if (!header.hasClass('ui-resizable')) {
            header.resizable({
                containment: ".paperA4.active",
                handles: {
                    's' : '#hsgrip'
                },
                stop: function () {
                    $('input[name="header_height"]').val(header.css('height'));
                }
            });

            $('input[name="header_height"]').change(function () {
                header.css('height', $('input[name="header_height"]').val());
            });
        }

        if (!footer.hasClass('ui-resizable')) {
            footer.resizable({
                containment: ".paperA4.active",
                handles: {
                    'n' : '#fngrip'
                },
                stop : function () {
                    footer.css('top', 'auto');
                    $('input[name="footer_height"]').val(footer.css('height'));
                }
            });

            $('input[name="footer_height"]').change(function () {
                footer.css('height', $('input[name="footer_height"]').val());
            });
        }

    };

    var initImageEvent = function() {
        $(".mteditor-image-list li img").draggable({
            helper: function(event) {
                var elemImg = $(event.currentTarget);
                return $('<img style="z-index: 99999" src="'+elemImg.attr('src')+'" />')
                    .addClass('active-draggable-element')
                    .data('element-type', 'element_image');
            },
            zIndex: 16777190,
            revert: false,
            stop: function() {
                createNewElement();
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

        $("#draggable a").draggable({
            helper: function(event) {
                var index = $(event.currentTarget).data('id');
                return $(config.elements[index]['html'])
                    .addClass('active-draggable-element')
                    .data('element-type', index);
            },
            zIndex: 16777190,
            revert: false,
            stop: function() {
                createNewElement();
            }
        });
    };

    var createNewElement = function () {
        var element = $('.active-draggable-element');
        if (!isInsidePaper(element)) {
            return false;
        }

        var paper = $('.paperA4.active');
        var elementPossition = element.offset();
        var paperPossition = paper.offset();
        var elementType = element.data('element-type');
        var newElement = $(config.elements[elementType]['html']);
        var elementId = guid();
        var zIndex = getTopLayerZIndex(paper);
        newElement.css({
            top: (elementPossition.top - paperPossition.top)+'px',
            left: (elementPossition.left - paperPossition.left)+'px',
            'z-index': zIndex
            })
            .addClass('pdf-element')
            .data('element-type', elementType)
            .data('element-id', elementId)
            .attr('id', elementId);

        if (elementType == 'element_image') {
            newElement.find('img').attr('src', element.attr('src'));
        }

        paper.append(newElement);
        pdfElement.reloadEvents(true);
    };

    var isInsidePaper = function (element) {
        var elementPossition = element.offset();
        var isInside = false;
        $('.paperA4').each(function () {
            var paperPossition = $(this).offset();
            if (elementPossition.left >= paperPossition.left
                && elementPossition.left <= (paperPossition.left+ $(this).width())
                && elementPossition.top >= paperPossition.top
                && elementPossition.top <= (paperPossition.top+ $(this).height())
            ) {
                isInside = true;
                return;
            }
        });

        return isInside;
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

        $('a[data-selector="edit-properties"]').unbind('click').click(function(){
            openPropertiesTools();
        });

        $('a[data-selector="edit-color"]').unbind('click').click(function(){
            openColorTools();
        });

        $('a[data-selector="edit-image"]').unbind('click').click(function(){
            openImageTools();
        });

        $('a[data-selector="edit-settings"]').unbind('click').click(function(){
            openSettingsTools();
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

            popup.content({
                contentSelector: '#preview_form',
                disableClose: false
            }, function(){
                var actionButton = $('#esns_box_content button[data-action="1"]');
                if ($('#esns_box_content select[name="preview_invoice_id"]').val()) {
                    actionButton.removeAttr('disabled');
                }

                var lastPreviewId = $.cookie('last_preview_id');
                if (lastPreviewId) {
                    $('select[name="preview_invoice_id"]').val(lastPreviewId);
                }

                $('select[name="preview_invoice_id"]').unbind('change').change(function(){
                    var invoiceId = $('#esns_box_content  select[name="preview_invoice_id"]').val();
                    if (!invoiceId) {
                        actionButton.attr('disabled', 'disabled');
                        return;
                    }
                    actionButton.removeAttr('disabled');
                });

                $('#esns_box_content .response-error').hide();
                $('#esns_box_content .response-success').hide();

                $('button[data-action="1"]').unbind('click').click(function(){
                    var invoiceId = $('#esns_box_content select[name="preview_invoice_id"]').val();
                    $.cookie('last_preview_id', invoiceId, { expires: 199, path: '/' });
                    $(this).text('Processing...');
                    saveHelper.save(function () {
                        window.location.href = config.action.previewUrl+'?invoice_id='+invoiceId;
                        hideLoading();
                    });
                });

                $('#esns_box_content a[data-action="0"]').click(function(e){
                    popup.close(true);
                });
            }, function() {
            }, function() {
            });
        });

        $('a[data-action="preview-variables"]').click(function(){
            popup.content({
                contentSelector: '#variable_form',
                disableClose: false
            }, function(){
                var actionButton = $('#esns_box_content button[data-action="1"]');
                if ($('#esns_box_content select[name="preview_invoice_id"]').val()) {
                    actionButton.removeAttr('disabled');
                }

                var lastPreviewId = $.cookie('last_preview_id');
                if (lastPreviewId) {
                    $('select[name="preview_invoice_id"]').val(lastPreviewId);
                }

                $('select[name="preview_invoice_id"]').unbind('change').change(function(){
                    var invoiceId = $('#esns_box_content  select[name="preview_invoice_id"]').val();
                    if (!invoiceId) {
                        actionButton.attr('disabled', 'disabled');
                        return;
                    }
                    actionButton.removeAttr('disabled');
                });

                $('#esns_box_content .response-error').hide();
                $('#esns_box_content .response-success').hide();

                $('button[data-action="1"]').unbind('click').click(function(){
                    var sourceId = $('#esns_box_content select[name="preview_invoice_id"]').val();


                    $.cookie('last_preview_id', sourceId, { expires: 199, path: '/' });
                    $(this).text('Processing...');

                    sendRequest(config.action.variableList+'?source_id='+sourceId, {
                        form_key: FORM_KEY,
                        id: config.template_id,
                        source_id: sourceId
                    }, function(response) {
                        if ($('#tmp_response')) {
                            $('#tmp_response').remove();
                        }
                        $('body').append($('<div id="tmp_response" style="display: none">'+response.data+'</div>'));
                             popup.content({
                                 contentSelector: '#tmp_response',
                                 disableClose: false
                             }, function(){
                                 popup.rePossition();
                             }, function() {
                             }, function() {
                             });
                    });
                });

                $('#esns_box_content a[data-action="0"]').click(function(e){
                    popup.close(true);
                });
            }, function() {
            }, function() {
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

        $('a[data-action="export-template"]').click(function(){
            var previewUrl = '';

            popup.content({
                contentSelector: '#export_template',
                disableClose: false
            }, function(){

                $('input[name="template[name]"]').unbind('keyup').keyup(function(){
                    var email = $(this).val();
                    var actionButton = $('#esns_box_content button[data-action="1"]');
                    if (!isTemplateNameValid(email)) {
                        actionButton.attr('disabled', 'disabled');
                        return;
                    }
                    actionButton.removeAttr('disabled');
                });

                $('#esns_box_content .response-error').hide();
                $('#esns_box_content .response-success').hide();

                $('button[data-action="1"]').unbind('click').click(function(){
                    var templateName = $('#esns_box_content input[name="template[name]"]').val();
                    if (isTemplateNameValid(templateName)) {

                        var button = $(this);
                        button.text('Exporting...');
                        $('#esns_box_content .response-error').hide();
                        $('#esns_box_content .response-success').hide();

                        sendRequest(config.action.exportUrl, {
                            form_key: FORM_KEY,
                            id: config.template_id,
                            template_name: templateName
                        }, function(response) {
                            if (response.success == 1) {
                                button.text('Downloading...');
                                window.location.href=response.download_link;
                            } else {
                                $('#esns_box_content .response-error').text(response.error).show();
                            }
                            popup.close(true);
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

        $('a[data-action="additional-page"]').click(function(){
            var previewUrl = '';
            popup.content({
                contentSelector: '#additional_page',
                disableClose: false
            }, function(){

                var elementsContainer = $('#esns_box_content .form-multiselect-items');
                var selectElement = elementsContainer.find('.form-multiselect').clone();
                elementsContainer.html('');

                var addNew = function (templateId) {
                    var newSelect = selectElement.clone().uniqueId();
                    newSelect.find('select').val(templateId);
                    $('#esns_box_content .form-multiselect-items').append(newSelect);
                    newSelect.find('.form-multiselect-remove').click(function () {
                        newSelect.remove();
                    });
                };

                $('#esns_box_content .form-multiselect-more').unbind('click').click(function () {
                    addNew(0);
                });

                $.each(config.additioanlPage, function (index, page) {
                    addNew(page.template_id);
                });

                $('#esns_box_content button[data-action="1"]').unbind('click').click(function() {
                    var additionalPages = [];
                    var i = 0;
                    $('#esns_box_content select[name="additional_page[]"]').each(function () {
                        additionalPages[i] = {
                            'template_id': $(this).val(),
                            'sort_order': i
                        };
                        i++;
                    });

                    var button = $(this);
                    button.text('Saving...');
                    $('#esns_box_content .response-error').hide();
                    $('#esns_box_content .response-success').hide();

                    sendRequest(config.action.saveAdditionalPage, {
                        form_key: FORM_KEY,
                        id: config.template_id,
                        additional_page: additionalPages
                    }, function(response) {
                        button.text('Save');
                        if (response.success == 1) {
                            config.additioanlPage = additionalPages;
                            $('#esns_box_content .response-success')
                                .text('Information has been saved successful!')
                                .show();
                        } else {
                            $('#esns_box_content .response-error').text(response.error).show();

                        }
                    });

                });

                $('#esns_box_content a[data-action="0"]').click(function(e){
                    popup.close(true);
                });
            }, function() {
            }, function() {
            });

        });


        $('a[data-action="import-template"]').click(function(){
            var previewUrl = '';

            popup.content({
                contentSelector: '#import_template',
                disableClose: false
            }, function(){
                $('#esns_box_content .importupload').fileupload({
                    singleFileUploads: true,
                    url: config.action.importUrl+'?isAjax=1',
                    formData: {form_key: FORM_KEY},
                    dropZone: undefined,
                    autoUpload: false,
                    add:function (e, data) {
                        $('#esns_box_content button[data-action="1"]').off('click').on('click',function () {
                            $('#esns_box_content button[data-action="1"]').text('Importing....');
                            data.submit();
                        });
                    }
                }).bind('fileuploadchange', function (e, data) {
                    var actionButton = $('#esns_box_content button[data-action="1"]');
                    actionButton.removeAttr('disabled');
                }).bind('fileuploaddone', function (e, data) {
                    $('#esns_box_content button[data-action="1"]').text('Processing...');
                    var result = data.result;
                    if (result.success == 1 && result.reload) {
                        window.location.reload();
                    }
                });

                $('#esns_box_content .response-error').hide();
                $('#esns_box_content .response-success').hide();

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
                $('#esns_box_content input[name="template_name"]').val(config.template.name);
                $('#esns_box_content input[name="template_locale"]').val(config.template.locale);
                $('#esns_box_content select[name="template_store_id"]').val(config.template.store_id);
            }, function(){
                $('#esns_box_layer a[data-action="1"]').text('Saving...');
                var newTemplateName = $('#esns_box_content input[name="template_name"]').val();
                var newTemplateLocale = $('#esns_box_content select[name="template_locale"]').val();
                var newStoreId = $('#esns_box_content select[name="template_store_id"]').val();
                sendRequest(config.action.saveInfo, {
                        form_key: FORM_KEY,
                        template_name: newTemplateName,
                        template_locale:  newTemplateLocale,
                        template_store_id:  newStoreId,
                        id: config.template_id
                    }, function(response) {
                        if (response.success == 1) {
                            config.template.name = newTemplateName;
                            config.template.locale = newTemplateLocale;
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

    var openSettingsTools = function() {
        beforOpenSettingsTools();
        openTools('edit-settings');
        $('#pdf_settings').show();
    };

    var openStyleTools = function() {
        beforeOpenStyleTools();
        openTools('edit-style');
    };

    var openPropertiesTools = function() {
        beforeOpenPropertiesTools();
        openTools('edit-properties');
    };

    var openColorTools = function() {
        beforeColorTools();
        openTools('edit-color');
    };


    var openTools = function(className) {
        var openPanel = '.tools.' + className;
        if ($(openPanel).hasClass('active')) {
            return false;
        }
        $('#pdf_settings').hide();
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

    var isTemplateNameValid = function(value) {
        if ( value.length >= 3) {
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
                content: content,
                vars: vars,
                css: css,
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

    var toHex = function(color) {

        if (!color) {
            return '#ffffff';
        }

        if (color == 'rgba(0, 0, 0, 0)' || color == 'transparent') {
            return '';
        }

        if (color.substr(0, 1) === '#') {
            return color;
        }
        var digits = /(.*?)rgb\((\d+), (\d+), (\d+)\)/.exec(color);
        if (digits == null) {
            var digits = /(.*?)rgba\((\d+), (\d+), (\d+), (\d+)\)/.exec(color);
        }
        var red = parseInt(digits[2]);
        var green = parseInt(digits[3]);
        var blue = parseInt(digits[4]);

        var rgb = blue | (green << 8) | (red << 16);
        var rColor =  digits[1] + '#' + rgb.toString(16);
        if (rColor == '#50808') { rColor = '#050808'; }
        return rColor;
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

    var beforOpenSettingsTools = function() {
        log('beforeOpen Image Tools');
        initSettings();
    };

    var beforeOpenLayoutTools = function() {
        log('beforeOpen Layout Tools');
        $('#email').sortable('enable');
    };

    var beforeOpenStyleTools = function() {
        initStyle();
    };

    var beforeOpenPropertiesTools = function() {
        initProperties();
    };

    var beforeColorTools = function() {
        initColorScheme();
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

    var guid = function () {
        var d = new Date();
        var n = d.getTime();

        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }

        return n+''+s4() + s4();
    };

    var getTopLayerZIndex = function (paper) {
        var maxZIndex = 117;
        paper.find('.pdf-element').each(function () {
            var zIndex = parseInt($(this).css('z-index'));
            if (!zIndex || zIndex < 117) {
                zIndex = 117;
            }
            if (zIndex > maxZIndex) {
                maxZIndex = zIndex;
            }
        });

        return maxZIndex+1;
    };

    var getPPI = function () {
        return ppi;
    };

    var initFonts = function () {
        var fonts = $('#mteditor_fonts');
        $('head').prepend('<style type="text/css">'+fonts.text()+'</style>');
        fonts.remove();
    };

    var initTemplateOptions = function () {
        $('.template_options').each(function () {
            var index = $(this).attr('name');
            if (!config.template[index]) {
                return;
            }
            if ($(this).is(':checkbox')) {
                if (config.template[index] == 1) {
                    $(this).trigger('click');
                }
            } else {
                $(this).val(config.template[index]).trigger('change');
            }
        });
    };

    return {
        init: init,
        config: config,
        getPPI: getPPI,
        log: log,
        initBlockEvent: initBlockEvent,
        initImage: initImage,
        openStyleTools: openStyleTools,
        opentPropertiesTools: openPropertiesTools,
        openImageTools: openImageTools,
        reloadEditorEvents: reloadEditorEvents,
        initPropertiesBlock: initPropertiesBlock
    };

})(jQuery);

function requirejs($a, $b) {
    return;
}

function require($a, $b) {
    return;
}