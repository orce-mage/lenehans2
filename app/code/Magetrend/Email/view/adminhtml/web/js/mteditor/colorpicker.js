/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */
var colorPicker = (function($){
    var config = {
        selector: '.color',
        color: {
            '#ac725e': '#ac725e',
            '#d06b64': '#d06b64',
            '#f83a22': '#f83a22',
            '#fa573c': '#fa573c',
            '#ff7537': '#ff7537',
            '#ffad46': '#ffad46',
            '#42d692': '#42d692',
            '#16a765': '#16a765',
            '#7bd148': '#7bd148',
            '#b3dc6c': '#b3dc6c',
            '#fbe983': '#fbe983',
            '#fad165': '#fad165',
            '#92e1c0': '#92e1c0',
            '#9fe1e7': '#9fe1e7',
            '#9fc6e7': '#9fc6e7',
            '#4986e7': '#4986e7',
            '#9a9cff': '#9a9cff',
            '#b99aff': '#b99aff',
            '#c2c2c2': '#c2c2c2',
            '#cabdbf': '#cabdbf',
            '#cca6ac': '#cca6ac',
            '#f691b2': '#f691b2',
            '#cd74e6': '#cd74e6',
            '#a47ae2': '#a47ae2',
            '#ffffff': '#ffffff',
            '#000000': '#000000'
        }
    };

    var init = function(options) {
        $.extend(config, options);
        setup();
    };

    var setup = function() {
        initPicker();
        initEvent();
    };

    var initPicker = function() {
        if (!$('#colorpicker').length) {
            $('body').append('<div id="colorpicker"><ul></ul></div>');
        }
        reloadPicker();
    };


    var reloadPicker = function() {
        $('#colorpicker ul').html('');
        $.each(config.color, function(key, value){
            $('#colorpicker ul').append('<li><span style="background-color: '+value+'">'+value+'</span></li>');
        });

        $('#colorpicker ul li').click(function(){
            var elm = $(this).find('span');
            var color = elm.text();
            updateColor (color);
            closePicker();
        });

        $('input.color').keyup(function(){
            var elm = $(this);
            if (elm.hasClass('active')) {
                updateColor ($(this).val());
            }
        })
    };

    var updateColor = function(color) {
        var elm = $(config.selector+'.active');
        if (elm.length == 0) {
            return;
        }
        elm.css({'background-color': color}).val(color);
        var fontColor = '#000000';
        if (isDarkColor(elm.css('background-color'))) {
            fontColor = '#ffffff';
        }
        elm.css('color', fontColor).trigger('change');
    };

    var initEvent = function() {
        $(config.selector).click(function(){
            showPicker($(this));
        });

        $(document).click(function(e) {
            if(!$(e.target).is('#colorpicker') && !$(e.target).is('.color')) {
                closePicker();
            }
        });
    };

    var showPicker = function(element) {
        var currentColor = element.val();
        if (currentColor && currentColor!= '') {
            if (!config.color[currentColor]) {
                addColor(currentColor);
            }
        }

        var pos = element.offset();
        var picker = $('#colorpicker');
        picker.show().css('top', '-9999px');

        if (picker.height() + pos.top + 26 < $(document).height()) {
            picker.css('top',(pos.top + 26)+'px');
        } else {
            picker.css('top',(pos.top - picker.height() - 12)+'px');
        }

        picker.css('left', pos.left+'px').show();
        $(config.selector+'.active').removeClass('active');
        element.addClass('active');

    };

    var closePicker = function() {
        $('#colorpicker').hide();
        $(config.selector+'.active').removeClass('active');
    };

    var addColor = function(colorCode) {
        if (colorCode.length == 7 && colorCode[0] == '#') {
            config.color[colorCode] = colorCode;
            reloadPicker();
        }
    };

    var isDarkColor = function(rgb) {
        var tmp = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        if (!tmp) {
            return false;
        }
        var count = 0;
        for (var i = 1; i <=3; i++) {
            if (parseInt(tmp[i]) < 125) {
                count++;
            }
        }

        if (count < 2) {
            return false;
        }

        return true;
    };

    return {
        init: init,
        isDarkColor: isDarkColor
    };
})(jQuery);