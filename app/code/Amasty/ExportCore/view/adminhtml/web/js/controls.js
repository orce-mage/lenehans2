define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'underscore'
], function (Abstract, $, _) {
    'use strict';

    return Abstract.extend({
        defaults: {
            exportButtonDisabled: false,
            messages: [],
            status: '',
            proceed: 0,
            total: 0,
            listens: {
                '${ $.parentName }:responseData': 'statusCheck'
            },
            modules: {
                formComponent: '${ $.parentName }',
            }
        },
        initObservable: function () {
            this._super().observe([
                'exportButtonDisabled',
                'messages',
                'status',
                'total',
                'proceed'
            ]);

            return this;
        },
        resetData: function () {
            this.exportButtonDisabled(false);
            this.status(null);
            this.proceed(0);
            this.total(0);
            this.messages([]);
        },
        exportData: function () {
            this.resetData();
            this.exportButtonDisabled(true);
            if (this.source.data.processIdentity !== undefined) {
                $.get(this.cancelUrl, {'processIdentity': this.source.data.processIdentity }, function () {
                    this.startExport();
                }.bind(this));
            } else {
                this.startExport();
            }
        },
        startExport: function () {
            this.source.data.processIdentity = this.uuidv4();
            this.formComponent().save();
            if (this.source.get('params.invalid')) {
                this.exportButtonDisabled(false);
            }
        },
        statusCheck: function () {
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
                }

                if (data.status === 'failed') {
                    this.exportButtonDisabled(false);
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
        getDownloadLink: function () {
            return this.downloadUrl.replace('_process_identity_', this.source.data.processIdentity);
        },
        regenerateExport: function () {
            this.exportData();
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
