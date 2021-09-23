define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function(targetModule){

        var getDescriptionId = targetModule.prototype.getDescriptionId;

        targetModule.prototype.getDescriptionId = wrapper.wrap(getDescriptionId, function(original){
            var id = original();

            if (id) {
                $('#'+this.uid).on('click keypress', function () {
                    $('#'+id).fadeOut();
                });
            }
        });

        return targetModule;
    };
});
