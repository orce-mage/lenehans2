/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
var textEditHelper = (function($){
    var config = {
        'classes': {
            'helperLink': 'mteditor-content-helper-link',
            'helperImg': 'mteditor-content-helper-img',
            'helperText': 'mteditor-content-helper-text',
            'helperSelected': 'mteditor-content-helper-selected',
            'helperContentImage': 'mteditor-content-helper-content-image'
        }
    };

    var init = function() {
        removeHelper();
        initEvent();
        initEditEvent();
        //initEditEvent();
    };


    var initEvent  = function() {

        $('#email').unbind('click').click(function(e) {
            e.preventDefault();
            mtEditor.log('click on content');
            var elm = $(e.target);

            if (!elm.is('img') && (elm.is('a') || elm.parents('a[contenteditable="false"]').length > 0)) {
                hide();
                if (elm.is('a')) {
                    addLinkHelper(elm);
                } else {
                    addLinkHelper(elm.parents('a[contenteditable="false"]'));
                }
                mtEditor.openStyleTools();
                show(elm, 'helper-link');
            } else if (elm.is('img')) {
                hide();
                addImgHelper(elm);
                mtEditor.openImageTools();
                show(elm, 'helper-image');

            } else if (elm.attr('contenteditable') == 'true') {
                hide();
                addTextHelper(elm);
                mtEditor.openStyleTools();
                show(elm, 'helper-text');
            } else if (elm.parents('*[contenteditable="true"]').length > 0) {
                hide();
                var parentElm = elm.parents('*[contenteditable="true"]');
                addTextHelper(parentElm);
                mtEditor.openStyleTools();
                show(parentElm, 'helper-text');
            } else {
                $('a[data-selector="edit-style"]').trigger('click');
                hide();
            }
        });

        $(document).unbind('click').on( "click", function( event ) {
            if (
                (
                $(event.target).closest("#email").length == 0
                && !$(event.target).closest(".edit-image").length
                && !$(event.target).parents('#editor_helper').length
                && !$(event.target).parents('.popup-content').length
                && !($('.'+config.classes.helperImg).length > 0 && $(event.target).is('a[data-selector="edit-image"]'))
                )
            ||
                $(event.target).closest('.block-action').length > 0
            ) {
                hide();
            }
        });

        $('.bock-action').mouseover(function(){
            hide();
        });
    };

    var show = function(elm, editorClass) {
        var pos = elm.offset();
        var posY = pos.top - $(document).scrollTop();
        var elmHeight = elm.height();
        var helperElm = $('#editor_helper');
        helperElm.hide();
        $('#editor_helper .helper').hide();
        $('#editor_helper .'+editorClass).show();

        if (editorClass == 'helper-image' && elm.hasClass('atr')) {
            $('#editor_helper .'+editorClass+ ' a[data-image-editable="true"]').parent('li').show();
        } else {
            $('#editor_helper .'+editorClass+ ' a[data-image-editable="true"]').parent('li').hide();
        }

        helperElm.css({
            top: -99999+'px'
        }).show();
        var height = helperElm.height();
        var top = 0;
        if (posY - height - 30 < 0) {
            top = pos.top + height + elmHeight + 10;
        } else {
            top = pos.top - height - 30;
        }

        helperElm.css({
            top: top +'px',
            left: pos.left+'px'
        });
    };

    var initEditEvent = function() {
        $('a[data-action="bold"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doBoldLink();
            } else if(isActiveText()) {
                doBoldText();
            }
        });

        $('a[data-action="italic"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doItalicLink();
            } else if(isActiveText()) {
                doItalicText();
            }
        });

        $('a[data-action="align-left"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doAlignLink('left');
            } else if(isActiveText()) {
                doAlignText('left');
            } else if(isActiveImg()) {
                doAlignImage('left');
            }

        });
        $('a[data-action="align-right"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doAlignLink('right');
            } else if(isActiveText()) {
                doAlignText('right');
            } else if(isActiveImg()) {
                doAlignImage('right');
            }

        });
        $('a[data-action="align-center"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doAlignLink('center');
            } else if(isActiveText()) {
                doAlignText('center');
            } else if(isActiveImg()) {
                doAlignImage('center');
            }

        });

        $('a[data-action="align-justify"]').unbind( "click" ).click(function() {
            if (isActiveLink()) {
                doAlignLink('justify');
            } else if(isActiveText()) {
                doAlignText('justify');
            } else if(isActiveImg()) {
                doAlignImage('justify');
            }

        });

        $('a[data-action="link"]').unbind( "click" ).click(function(e) {
            e.preventDefault();

            if (
                $(this).parents('.block-action').length  > 0
                && elm.data('action')!= 'link'
            ) {
                return true;
            }

            if (isActiveLink()) {
                doLink('link');
            } else if(isActiveText()) {
                doLink('text');
            } else if(isActiveImg()) {
                doLink('img');
            }
        });

        $('a[data-action="var"]').unbind( "click" ).click(function(e) {
            e.preventDefault();
            if (isActiveText()) {
                doVar();
            }
        });


        $('a[data-action="html"]').unbind( "click" ).click(function() {
            popup.content({
                contentSelector: '#edit_html',
                disableClose: false
            }, function(){
                $('#esns_box_layer textarea[name="html"]').val($.trim($('#email .mteditor-content-helper-text').html()));
            }, function() {
                $('#email .mteditor-content-helper-text').html($('#esns_box_layer textarea[name="html"]').val());

            }, function() {
            });
        });

        $('a[data-action="image"]').unbind( "click" ).click(function(e) {
            e.preventDefault();
            if (isActiveText()) {
                initContentImageHelper();
                mtEditor.openImageTools();

            }
        });

        $('a[data-action="remove-image"]').unbind( "click" ).click(function(e) {
            e.preventDefault();
            if (isActiveImg()) {
                removeImage();
            }
        });
    };

    var removeImage = function () {
        $('.'+config.classes.helperImg).remove();
        mtEditor.openStyleTools();
        hide();
    };

    var replaceSelectedContent = function (html) {
        var range, html;
        if (window.getSelection && window.getSelection().getRangeAt) {
            range = window.getSelection().getRangeAt(0);
            range.deleteContents();
            var div = document.createElement("div");
            div.innerHTML = html;
            var frag = document.createDocumentFragment(), child;
            while ( (child = div.firstChild) ) {
                frag.appendChild(child);
            }
            range.insertNode(frag);
        } else if (document.selection && document.selection.createRange) {
            range = document.selection.createRange();
            range.pasteHTML(html);
        }
    //    initEditEvent();
    };

    var replaceHelperContentHtml = function(html) {
        $('.mteditor-content-helper-selected').html(html);
     //   initEditEvent();
    };

    var hide = function() {
        $('.editor-helper-active').removeClass('editor-helper-active');
        $('.mteditor-content-helper-selected').replaceWith($('.mteditor-content-helper-selected').html());
        $('#editor_helper').hide();
        $('#editor_helper .helper').hide();
        removeHelper();
    };

    var getSelectionHtml = function () {
        var html = "";
        if (typeof window.getSelection != "undefined") {
            var sel = window.getSelection();
            if (sel.rangeCount) {
                var container = document.createElement("span");
                for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                    container.appendChild(sel.getRangeAt(i).cloneContents());
                }
                html = container.innerHTML;
            }
        } else if (typeof document.selection != "undefined") {
            if (document.selection.type == "Text") {
                html = document.selection.createRange().htmlText;
            }
        }
        return html;
    };

    var initLinkForm = function(elmType) {

        $('#esns_box_content .form-group').show();
        if (elmType == 'link') {
            var link = $('.'+config.classes.helperLink);
            $('input[name="editor_link_href"]').val(link.attr('href'));
            $('input[name="editor_link_title"]').val($.trim(link.html()));
            if (link.data('disable-remove') == 1) {
                $('#esns_box_content *[data-action="0"]').hide();
            } else {
                $('#esns_box_content *[data-action="0"]').show();
            }
        } else if (elmType == 'text') {
            $('input[name="editor_link_title"]').val($('.'+config.classes.helperSelected).html());
        } else if (elmType == 'img') {
            var elm = $('.'+config.classes.helperImg);
            if (elm.parent().is('a')) {
                $('input[name="editor_link_href"]').val(elm.parents('a').attr('href'));
            }
            $('#esns_box_content .form-group.link-title').hide();
        }
    };

    var updateLink = function(type) {

        var href = $('#esns_box_content input[name="editor_link_href"]').val();
        var val = $('#esns_box_content input[name="editor_link_title"]').val();

        if (type == 'text') {
            $('.'+config.classes.helperSelected).replaceWith('<a contenteditable="false" href="'+href+'">'+val+'</a>');
        } else if (type == 'link') {
            $('.'+config.classes.helperLink).html(val).attr('href', href);
        } else if (type == 'img') {
            var elm = $('.'+config.classes.helperImg);
            if (elm.parent().is('a')) {
                elm.parent().attr('href', href);
            } else {
                elm.replaceWith('<a contenteditable="false" href="'+href+'">'+elm.wrap("<span></span>").parent().html()+'</a>');
            }
            mtEditor.initImage();
        }

    };

    var removeLink = function(type) {
        if (type == 'link') {
            $('.'+config.classes.helperLink).replaceWith($('.'+config.classes.helperLink).html());
        } else if (type == 'img') {
            var elm = $('.'+config.classes.helperImg);
            if (elm.parent().is('a')) {
                elm.parent().replaceWith(elm.parent().html());
                mtEditor.initImage();
            }
        }
    };

    var initContentHelper = function() {
        if ($('.'+config.classes.helperSelected).length  ==  0) {
            replaceSelectedContent('<span class="'+config.classes.helperSelected+'">'+getSelectionHtml()+'</span>');
        }
    };

    var initContentImageHelper = function() {
        if ($('.'+config.classes.helperContentImage).length > 0){
            $('.'+config.classes.helperContentImage).remove();
        }
        replaceSelectedContent('<span class="'+config.classes.helperContentImage+'" style="display: none;"></span>');
    };

    var addLinkHelper = function(elm) {
        elm.addClass(config.classes.helperLink);
    };

    var addImgHelper = function(elm) {
        elm.addClass(config.classes.helperImg);
    };

    var addTextHelper = function(elm) {
        elm.addClass(config.classes.helperText);
    };

    var isActiveLink = function() {
        return $('.'+config.classes.helperLink).length > 0;
    };

    var isActiveImg = function() {
        return  $('.'+config.classes.helperImg).length > 0;
    };

    var isActiveText = function() {
        return $('.'+config.classes.helperText).length > 0;
    };

    var removeHelper = function() {
        mtEditor.log('remove all helpers');
        $('.'+config.classes.helperImg).removeClass(config.classes.helperImg);
        $('.'+config.classes.helperLink).removeClass(config.classes.helperLink);
        $('.'+config.classes.helperText).removeClass(config.classes.helperText);
        $('.'+config.classes.helperContentImage).remove();
    };

    var doBoldLink = function () {
        var currentElm = $('.'+config.classes.helperLink);
        if (currentElm.css('font-weight') == 700) {
            currentElm.css('font-weight','');
        } else {
            currentElm.css('font-weight','700');
        }
    };

    var doBoldText = function () {
        var selectedContent = getSelectionHtml();
        if (!selectedContent) {
            var currentElm = $('.'+config.classes.helperText);
            if (currentElm.css('font-weight') == 700) {
                currentElm.css('font-weight','');
            } else {
                currentElm.css('font-weight','700');
            }
        } else {
            if (selectedContent.split('<b>').length > 1) {
                selectedContent = selectedContent.replace('<b>','').replace('</b>','');
            } else {
                selectedContent = '<b>'+selectedContent+'</b>';
            }
            replaceSelectedContent(selectedContent);
        }
    };

    var doItalicLink = function () {
        var currentElm = $('.'+config.classes.helperLink);
        if (currentElm.css('font-style') == 'italic') {
            currentElm.css('font-style','');
        } else {
            currentElm.css('font-style','italic');
        }
    };

    var doItalicText = function () {
        var selectedContent = getSelectionHtml();
        if (!selectedContent) {
            var currentElm = $('.'+config.classes.helperText);
            if (currentElm.css('font-style') == 'italic') {
                currentElm.css('font-style','');
            } else {
                currentElm.css('font-style','italic');
            }
        } else {
            if (selectedContent.split('<i>').length > 1) {
                selectedContent = selectedContent.replace('<i>','').replace('</i>','');
            } else {
                selectedContent = '<i>'+selectedContent+'</i>';
            }
            replaceSelectedContent(selectedContent);
        }
    };

    var doAlignLink = function (align) {
        $('.'+config.classes.helperLink).css('text-align', align);
    };

    var doAlignText = function (align) {
        $('.'+config.classes.helperText).css('text-align', align);
    };

    var doAlignImage = function (align) {
        $('.'+config.classes.helperImg).css('text-align', align).attr('align', align);
    };

    var doLink = function (elmType) {
        if (elmType == 'text') {
            initContentHelper();
        }
        popup.content({
            contentSelector: '#edit_link_form'
        }, function(){
           initLinkForm(elmType);
        }, function(){
            updateLink(elmType);
            hide();
        }, function(){
            removeLink(elmType);
            hide();
            removeHelper();
        });
    };

    var doVar = function () {

        initContentHelper();
        popup.content({
            contentSelector: '#edit_var_form'
        }, function(){

        }, function(){
            insertVar();
          //  hide();
        }, function(){
         //   removeLink(elmType);
          //  hide();
          //  removeHelper();
        });
    };

    var insertVar = function() {
        var helper = $('.mteditor-content-helper-selected');
        if (helper.length == 0) {
            return;
        }
        helper.replaceWith($('#esns_box_layer select[name="editor_var"]').val());
    };

    return {
        init: init,
        config: config,
        initLinkForm: initLinkForm,
        updateLink: updateLink,
        show: show,
        replaceSelectedContent: replaceSelectedContent,
        hide: hide
    };
})(jQuery);
