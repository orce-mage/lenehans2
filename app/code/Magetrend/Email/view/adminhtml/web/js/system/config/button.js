/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */
require([
    'prototype'
], function () {
    window.mtEmailMass = function (url, storeId) {
        var r = confirm("Are you sure? ");
        if (r == true) {
            new Ajax.Request(url, {
                parameters: {
                    'store_id': storeId,
                    'design': document.getElementById('mtemail_mass_action_design').value
                },
                loaderArea: container,
                onComplete: function (transport) {
                    if (transport.responseJSON.message) {
                        alert(transport.responseJSON.message);
                    }
                    if (transport.responseJSON.error) {
                        alert('Error: ' + transport.responseJSON.error);
                    }
                }.bind(this)
            });
        }
    };

    document.observe("dom:loaded", function() {
        var optionSelectors = ['mtemail_demo_order_id', 'mtemail_demo_invoice_id', 'mtemail_demo_creditmemo_id', 'mtemail_demo_shipment_id'];
        optionSelectors.forEach(function(e, i) {
            var buttonElement = $$('#row_'+ e +' .look-up-entity')
            if (buttonElement.length > 0) {
                buttonElement.invoke('on', 'click',  function(event, el) {
                    $('row_'+ e).hide();
                    $('row_'+ e + '_dummy').setStyle({
                        'display': 'table-row'
                    });

                    $$('#row_'+ e + '_dummy select').invoke('stopObserving', 'change').invoke('observe', 'change', function() {
                        if ($(this).value == 'custom') {
                            $('row_'+ e + '_dummy').hide();
                            $('row_'+ e).setStyle({
                                'display': 'table-row'
                            });
                        } else {
                            $(e).setAttribute('value', $(this).value);
                        }
                    });
                    $(e).setAttribute('value', $(e + '_dummy').value);
                });
            }
        });
    });

});
