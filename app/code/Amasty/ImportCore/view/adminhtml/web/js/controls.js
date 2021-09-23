define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'underscore'
], function (Abstract, $, _) {
    'use strict';

    return Abstract.extend({
        defaults: {
            messages: [],
            status: '',
            proceed: 0,
            total: 0,
            isImport: false,
            listens: {
                '${ $.parentName }:responseData': 'statusCheck'
            },
            modules: {
                formComponent: '${ $.parentName }',
            }
        },
        initObservable: function () {
            this._super().observe([
                'messages',
                'isImport',
                'status',
                'total',
                'proceed'
            ]);

            return this;
        },
        resetData: function () {
            this.status(null);
            this.proceed(0);
            this.total(0);
            this.messages([]);
        },
        checkData: function () {
            this.resetData();
            this.visible(false);
            this.source.data.processIdentity = this.uuidv4();
            this.formComponent().save();
            if (!this.source.get('params.invalid')) {
                $('.amimport-check-data').prop('disabled', true);
                this.changeFormElementsState(true, this.formComponent().elems());
            }
        },
        cancel: function () {
            $.get(this.cancelUrl, {'processIdentity': this.source.data.processIdentity }, function () {
                $('.amimport-check-data').prop('disabled', false);
                this.changeFormElementsState(false, this.formComponent().elems());
            }.bind(this));
        },
        cancelClick: function () {
            this.visible(false);
            this.cancel();
        },
        processImport: function () {
            _.each(this.formComponent().elems(), function (elem) {
                if (elem.name !== this.name) {
                    elem.visible(false);
                }
            }.bind(this));
            this.isImport(true);
            this.resetData();
            $.get(this.importUrl, {'processIdentity': this.source.data.processIdentity }, function () {
                this.statusCheck();
            }.bind(this));
        },
        statusCheck: function () {
            this.visible(true);
            this.getStatus().done(function (data) {
                this.status(data.status);
                this.total(data.total);
                this.proceed(data.proceed);

                if (data.messages !== undefined) {
                    this.messages(data.messages)
                } else {
                    this.messages([]);
                }

                if (data.status === 'running' || data.status === 'starting') {
                    setTimeout(this.statusCheck.bind(this), 1000);
                } else {
                    if (this.status() === 'failed') {
                        this.cancel();
                    }
                }
            }.bind(this));
        },
        getStatus: function () {
            var result = $.Deferred();
            $.get(this.statusUrl, {'processIdentity': this.source.data.processIdentity }, function (data) {
                result.resolve(data);
            });

            return result;
        },
        changeFormElementsState: function (disable, elems) {
            _.each(elems, function (elem) {
                if (_.isFunction(elem.visible) && elem.visible() && _.isFunction(elem.disabled)) {
                    elem.disabled(disable);
                }
                if (_.isFunction(elem.elems)) {
                    if (!_.isFunction(elem.disabled) && elem.disabled === false) {
                        this.changeFormElementsState(disable, elem.elems());
                    }
                }
            }.bind(this));
        },
        uuidv4: function () {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(
                /[xy]/g,
                function (c) {
                    var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                }
            );
        }
    });
});
