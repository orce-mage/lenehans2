define([
    'Magento_Ui/js/form/element/abstract',
    'underscore'
], function (Input, _) {
    return Input.extend({
        defaults: {
            cols: 10,
            rows: 4,
            textareaElementTmpl: 'ui/form/element/textarea',
            inputElementTmpl: 'ui/form/element/input',
            textareaNotice: '',
            inputNotice: '',
            notice: '',
            imports: {
                'parentCondition': '${ $.provider }:${ $.parentScope }.condition'
            },
            isTextarea: false,
            listens: {
                'parentCondition': 'conditionChanged'
            }
        },
        initObservable: function () {
            this._super().observe(['isTextarea', 'parentCondition', 'notice']);

            return this;
        },
        conditionChanged: function () {
            if (_.contains(['in', 'nin'], this.parentCondition())) {
                this.notice(this.textareaNotice);
                this.isTextarea(true);
            } else {
                this.notice(this.inputNotice);
                this.isTextarea(false);
            }
        }
    });
});
