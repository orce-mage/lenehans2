/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
require([
    'prototype'
], function () {
    window.mtEmailMass = function (url, storeId) {
        var r = confirm("Are you sure? ");
        if (r == true) {
            new Ajax.Request(url, {
                parameters: {
                    'store_id': storeId
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
});
