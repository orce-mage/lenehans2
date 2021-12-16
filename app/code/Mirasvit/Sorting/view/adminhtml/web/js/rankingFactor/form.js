define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'uiRegistry'
], function ($, _, Form, Registry) {
    return Form.extend({
        defaults: {
            imports: {
                type:     '${ $.provider }:data.type',
                isGlobal: '${ $.provider }:data.is_global',
                notes:    '${ $.provider }:notes'
            },
            
            listens: {
                type:     'updateType',
                isGlobal: 'updateIsGlobal'
            }
        },
        
        initialize: function () {
            this._super();
            
            _.bindAll(
                this,
                'updateType',
                'updateIsGlobal'
            );
            
            setInterval(this.updateType, 100);
            setInterval(this.updateIsGlobal, 100);
        },
        
        updateType: function () {
            var typeEl = Registry.get(this.name + ".general.type");
            
            if (typeEl && this.notes && _.isFunction(typeEl.notice)) {
                typeEl.notice(this.notes[this.type])
            }
        },
        
        updateIsGlobal: function () {
            var g = parseInt(this.isGlobal);
            
            if (g) {
                this.show('weight');
            } else {
                this.hide('weight');
            }
        },
        
        hide: function (field) {
            var $el = $('[data-index="' + field + '"]');
            $el.hide();
            
            return this;
        },
        
        show: function (field) {
            var $el = $('[data-index="' + field + '"]');
            $el.show();
            
            return this;
        }
    });
});