require([
    'jquery',
    'ko',
    'MageBig_SyntaxCms/cm/lib/codemirror',
    'MageBig_SyntaxCms/cm/mode/htmlmixed/htmlmixed',
    'MageBig_SyntaxCms/cm/mode/magento/magento',
    'MageBig_SyntaxCms/cm/mode/php/php',
], function ($, ko, cm) {

    ko.bindingHandlers.snm_syntaxcms = {
        init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
            var mode = ko.unwrap(valueAccessor() || 'magento');
            var code = element.innerHTML;
            code = code.replace(/^\s+|\s+$/g, "");
            code = code.replace(/\\{/g, '{');
            code = code.replace(/\\}/g, '}');
            code = code.replace(/\[\%lt\%\]/g, '<');
            code = code.replace(/\[\%gt\%\]/g, '>');
            code = code.replace(/\[\%amp\%\]/g, '&');
            code = code.replace(/^<!--/, '');
            code = code.replace(/--\>$/, '');

            if (mode == 'phtml') {
                mode = 'php';
            }

            element.innerHTML = '';
            var c = cm(element,
                {
                    lineNumbers: true,
                    readOnly: true,
                    value: code,
                    mode: mode
                }
            );
            if ($(element).hasClass('snm-cm-wrap-autoheight')) {
                c.on('update', function (cm, from, to) {
                    var h = jQuery(c.display.sizer).height();
                    if (h < jQuery(c.display.scroller).height())
                        h = jQuery(c.display.scroller).height();
                    $(cm.display.wrapper).parent().height(h);
                });
                var h = jQuery(c.display.sizer).height();
                if (h < jQuery(c.display.scroller).height())
                    h = jQuery(c.display.scroller).height();
                $(c.display.wrapper).parent().height(h);
            }
        },
        update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
        }
    };

});
