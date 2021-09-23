/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
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

    var autoSave = false;

    var init = function(options) {
        $.extend(config, options);
        setup();
    };

    var setup = function() {
        initEvent();
    };

    var initEvent = function() {
        if (config.autoSave == true) {

        }

        $('button[data-action="save"]').unbind('click').click(function(){
            if (canSave) {
                save();
            }
        });
    };

    var save = function(callBack) {
        var content = getPreparedContent();
        var vars = getContentVars();
        var css = getCss();
        var applyToAll = $('#switch_apply_to_all').hasClass('active')?1:0;
        $('*[data-action="save"]').text('Saving...');
        sendRequest(
            config.action.saveUrl,
            {
                template_id: config.template_id,
                vars: JSON.stringify(vars),
                css: JSON.stringify(css),
                template_content: JSON.stringify(content),
                apply_to_all: applyToAll,
                removed_block_list: mtEditor.removedBlockList
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

    var getCss = function() {
        return $('#email_css').html();
    };

    var getContentVars = function() {
        var vars = {};
        $('#email table[data-block-id]').each(function () {
            var blockId = $(this).data('block-id');
            var blockName = $(this).data('block-name');
            if (!vars[blockName]) {
                vars[blockName] = {};
            }
            vars[blockName][blockId] = {};
            $(this).parent().find('*[data-var-style]').each(function(){
                var varKey = $(this).data('var-style');
                vars[blockName][blockId][varKey] = $(this).attr('style');
            });

            $(this).parent().find('*[data-var-bgcolor]').each(function(){
                var varKey = $(this).data('var-bgcolor');
                vars[blockName][blockId][varKey] = $(this).attr('bgcolor');
            });

            $(this).parent().find('*[data-var-text]').each(function(){
                var varKey = $(this).data('var-text');
                vars[blockName][blockId][varKey] = $(this).html();
            });

            $(this).parent().find('*[data-var-src]').each(function(){
                var varKey = $(this).data('var-src');
                vars[blockName][blockId][varKey] = $(this).attr('src');
            });

            $(this).parent().find('*[data-var-alt]').each(function(){
                var varKey = $(this).data('var-alt');
                vars[blockName][blockId][varKey] = $(this).attr('alt');
            });

            $(this).parent().find('*[data-var-title]').each(function(){
                var varKey = $(this).data('var-title');
                vars[blockName][blockId][varKey] = $(this).attr('title');
            });

            $(this).parent().find('*[data-var-href]').each(function(){
                var varKey = $(this).data('var-href');
                vars[blockName][blockId][varKey] = $(this).attr('href');
            });

            $(this).parent().find('*[data-var-width]').each(function(){
                var varKey = $(this).data('var-width');
                vars[blockName][blockId][varKey] = $(this).attr('width');
            });

            $(this).parent().find('*[data-var-height]').each(function(){
                var varKey = $(this).data('var-height');
                vars[blockName][blockId][varKey] = $(this).attr('height');
            });
        });

        mtEditor.log(vars);
        return vars;
    };

    var getPreparedContent = function() {
        var content = getContent();
        $('#hidden_content').append('<div id="tmp_content">'+content+'</div>');

        $('#tmp_content .block-action').remove();
        $('#tmp_content .empty-placeholder').remove();
        $('#tmp_content table[data-block]').each(function(){
             $(this).replaceWith($.trim($(this).find('td').html()));
        });

        //restore facke block data
       // $('#tmp_content *[data-fake-content]').each(function(){
        $.each(mtEditor.config.data, function(index, value){
            var originContent = value.content;
            $('#tmp_content *[data-block-name="'+index+'"]').each(function(){
                var block = $(this);
                block.replaceWith(originContent.replace('block_id=0', 'block_id='+block.attr('data-block-id')));
            });

        });

        var emailContent = $.trim($('#tmp_content').html());
        $('#tmp_content').remove();
        return emailContent;
    };

    var getContent = function() {
        return $('#email').clone().html();
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

    var startAutoSave = function() {
        autoSave = true;
        saveHelper.autoSave();
    };

    var autoSave = function() {
        if (autoSave && canSave) {
            saveHelper.save();
            setTimeout('saveHelper.autoSave()', 10000);
        }
    };

    var stopAutoSave = function() {
        autoSave = false;
    };

    return {
        init: init,
        save: save,
        autoSave: autoSave,
        stopAutoSave: stopAutoSave,
        startAutoSave: startAutoSave,
        getPreparedContent: getPreparedContent,
        getContentVars: getContentVars,
        getCss: getCss

    };
})(jQuery);