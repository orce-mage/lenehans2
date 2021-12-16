define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/components/button',
    'uiRegistry'
], function ($, _, Button, Registry) {
    return Button.extend({
        defaults: {
            title: 'Save & Continue',
            
            _type: '',
            
            imports: {
                type: '${ $.provider }:data.type'
            },
            
            listens: {
                type: 'updateType'
            }
        },
        
        initialize: function () {
            this._super();
            
            _.bindAll(
                this,
                'updateType'
            );
            
            this._type = this.type;
            
            this.updateType();
        },
        
        
        updateType: function () {
            var section = Registry.get('sorting_rankingFactor_form.sorting_rankingFactor_form.config');
            
            if (!_.isString(this.type)) {
                this.visible(true);
                this.setVisibility(section, false);
                
                return
            }
            
            if (this.type === "" || this.type !== this._type) {
                this.visible(true);
                this.setVisibility(section, false);
            } else {
                this.visible(false);
                this.setVisibility(section, true);
            }
        },
        
        action: function () {
            var form = Registry.get('sorting_rankingFactor_form.sorting_rankingFactor_form');
            form.save()
        },
        
        setVisibility: function (element, visible) {
            if (!element) {
                return;
            }
            
            element.visible(visible);
            
            _.each(element.elems(), function (elem) {
                if (elem && _.isFunction(elem.visible)) {
                    elem.visible(visible);
                }
            }.bind(this));
        }
    });
});