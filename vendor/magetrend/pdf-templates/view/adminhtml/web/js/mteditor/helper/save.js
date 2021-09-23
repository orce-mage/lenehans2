/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
var saveHelper = (function($){
    var config = {

        template_id: 0,

        action: {
            saveUrl: ''
        },
        formKey: ''
    };

    var canSave = true;

    var init = function(options) {
        $.extend(config, options);
        setup();
    };

    var setup = function() {
        initEvent();
    };

    var initEvent = function() {
        $('button[data-action="save"]').unbind('click').click(function(){
            if (canSave) {
                save();
            }
        });
    };

    var save = function(callBack) {
        var elements = getElements();
        elements = JSON.stringify(elements);
        var templateOptions = getTemplateOptions();
        var applyToAll = 0;
        $('*[data-action="save"]').text('Saving...');
        sendRequest(
            config.action.saveUrl,
            {
                form_key: FORM_KEY,
                template_id: config.template_id,
                template_ppi: mtEditor.getPPI(),
                template_options: templateOptions,
                elements: elements
            },
            function(response) {
                if (callBack) {
                    callBack(response);
                }

                $('*[data-action="save"]').text('Save');
                mtEditor.removedBlockList = {};
            }
        )
    };

    var getTemplateOptions = function () {
        var templateOptions = {};
        $('.template_options').each(function () {
            var index = $(this).attr('name');
            var value = 0;
            if ($(this).is(':checkbox')) {
                if ($(this).is(':checked')) {
                    value = 1;
                }
            } else {
                value = $(this).val();
            }
            templateOptions[index] = value;
        });
        return templateOptions;
    };


    var getElements = function () {
        var pageId = 1;
        var elementsData = {};
        var elementCounter = 0;
        $('.paperA4').each(function () {
            var pageElement = $(this);
            pageElement.find('.pdf-element').each(function () {
                elementsData[elementCounter] = collectElementData($(this), pageId);
                elementCounter++;
            });
            pageId++;
        });
        return elementsData;
    };

    var collectElementData = function (element, pageId) {
        var elementType = element.data('element-type');
        var elementId = element.attr('id');
        var sortOrder = element.css('z-index');
        if (!sortOrder || sortOrder < 117 || sortOrder == 'auto') {
            sortOrder = 117;
        }
        var elementConfig = mtEditor.config.elements[elementType]['config'];
        var elementData = {
            id: elementId,
            page_id: pageId,
            type: elementType,
            sort_order: sortOrder,
            attributes: {}
        };

        if (elementConfig['attributes']) {
            $.each(elementConfig.attributes, function (code, options) {
                if (options['onSave']) {
                    elementData['attributes'][code]  = eval(
                        options.onSave+'(\''+elementId+'\', \''+options.className+'\', \''+options.attribute+'\');'
                    );
                }
            });
        }
        return elementData;
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

    return {
        init: init,
        save: save,
    };

})(jQuery);