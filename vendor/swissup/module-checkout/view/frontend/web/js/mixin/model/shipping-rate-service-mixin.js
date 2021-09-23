define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/shipping-rate-processor/customer-address'
], function (wrapper, defaultProcessor, customerAddressProcessor) {
    'use strict';

    // copied from Magento_Checkout/js/model/shipping-rate-service
    var processors = {
        default: defaultProcessor,
        'customer-address': customerAddressProcessor
    };

    return function (target) {
        target.registerProcessor = wrapper.wrap(
            target.registerProcessor,
            function (original, type, processor) {
                processors[type] = processor;

                return original(type, processor);
            }
        );

        /**
         * @param  {String}  type
         * @param  {Boolean} strict
         * @return {Object|Undefined}
         */
        target.getProcessor = function (type, strict) {
            if (!processors[type] && !strict) {
                return processors.default;
            }

            return processors[type];
        };

        /**
         * @return {Object}
         */
        target.getProcessors = function () {
            return processors;
        };

        return target;
    };
});
