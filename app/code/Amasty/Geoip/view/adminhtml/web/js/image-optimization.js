define([
    'uiElement',
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert'
], function (Element, $, $t, message) {
    'use strict';

    return Element.extend({
        defaults: {
            importItems: [],
            downloadStatusWidth: 0,
            initialDownloadPercent: 0,
            importStatusWidth: 0,
            downloadCounter: '',
            importCounter: '',
            downloadStarted: false,
            isDownloadCompleted: false,
            initialStatus: false,
            importDone: false,
            isShowImportCounter: false,
            isShowDownloadCounter: false,
            message: '',
            messages: {
                upToDate: $t('GeoIp data is up-to-date.'),
                error: $t('Error.')
            }

        },

        initialize: function () {
            this._super();

            this.initialStatus(this.getInitialStatus());
            this.downloadStatusWidth(this.initialDownloadPercent);
            this.importStatusWidth(this.initialDownloadPercent);
            this.importDone(this.initialStatus());
            this.isDownloadCompleted(this.initialStatus());
            this.downloadStep = 1;
            this.urls = [];
            this.isDownload = false;
        },

        initObservable: function () {
            this._super().observe([
                'downloadStatusWidth',
                'downloadCounter',
                'importStatusWidth',
                'downloadStarted',
                'isDownloadCompleted',
                'importDone',
                'importCounter',
                'isShowImportCounter',
                'isShowDownloadCounter',
                'initialStatus',
                'message'
            ]);

            return this;
        },

        getInitialStatus: function () {
            return !!this.initialDownloadPercent;
        },

        initImportAndDownload: function () {
            this.initialStatus(false);
            this.isDownloadCompleted(false);
            this.importDone(false);
            this.importStatusWidth(0);
            this.importItems.each(function (item) {
                this.runDownloading(item.start, item.process, item.commit, item.download);
            }.bind(this));
        },

        initImport: function () {
            this.importDone(false);
            this.importStatusWidth(0);
            this.importItems.each(function (item) {
                this.run(item.start, item.process, item.commit, item.download);
            }.bind(this));
        },

        runDownloading: function (startUrl, processUrl, commitUrl, startDownloadingUrl) {
            this.isDownload = true;
            this.isShowDownloadCounter(true);
            this.downloadStarted(true);
            this.downloadStatusWidth(30);
            this.downloadCounter('0/3');

            $.ajax({
                url: startDownloadingUrl,
                type: 'POST',
                dataType: 'json',
                data: { form_key: FORM_KEY }
            }).done($.proxy(function (response) {
                switch (response.status) {
                    case 'finish_downloading':
                        this.doneDownloading(startUrl, processUrl, commitUrl);
                        break;
                    case 'done':
                        this.isShowDownloadCounter(false);
                        this.downloadStatusWidth(100);
                        this.isDownloadCompleted(true);
                        this.importDone(true);
                        this.initialStatus(true);
                        this.importStatusWidth(100);
                        this.message(this.messages.upToDate);
                        break;
                    case response.error:
                        this.isShowDownloadCounter(false);
                        this.message(this.messages.error);
                        this.downloadStarted(false);
                        this.downloadStatusWidth(0);
                        message({ content: response.error });
                        break;
                    default:
                        this.isShowDownloadCounter(false);
                        this.downloadStarted(false);
                }
            }, this));
        },

        doneDownloading: function (startUrl, processUrl, commitUrl) {
            if (this.downloadStep === 3) {
                this.downloadStatusWidth(100);
                this.isDownloadCompleted(true);
                this.isShowDownloadCounter(false);

                this.urls.each(function (type) {
                    this.run(type[0], type[1], type[2]);
                }.bind(this));

                return this.run(startUrl, processUrl, commitUrl);
            }

            this.downloadStatusWidth(this.downloadStep * 30);
            this.downloadCounter(this.downloadStep + '/3');

            this.urls.push([startUrl, processUrl, commitUrl]);

            this.downloadStep++;
        },

        run: function (startUrl, processUrl, commitUrl) {
            $.ajax({
                url: startUrl,
                type: 'POST',
                dataType: 'json',
                data: { form_key: FORM_KEY, is_download: this.isDownload }
            }).done(function (response) {
                if (response.status === 'started') {
                    this.isShowImportCounter(true);
                    this.importCounter('0%');
                    this.process(processUrl, commitUrl);
                } else if (response.error) {
                    this.importError(response);
                }
            }.bind(this));
        },

        process: function (processUrl, commitUrl) {
            $.ajax({
                url: processUrl,
                type: 'POST',
                dataType: 'json',
                data: { form_key: FORM_KEY }
            }).done(function (response) {
                if (response.status == 'processing') {
                    if (response.type === 'block') {
                        this.importStatusWidth(response.position);
                        this.importCounter(response.position + '%');
                    }

                    if (response.position == 100) {
                        return this.commit(commitUrl);
                    }

                    this.process(processUrl, commitUrl);
                } else if (response.error) {
                    this.importError(response);
                }
            }.bind(this));
        },

        commit: function (commitUrl) {
            $.ajax({
                url: commitUrl,
                type: 'POST',
                dataType: 'json',
                data: { form_key: FORM_KEY }
            }).done(function (response) {
                if (response.status == 'done') {
                    this.done(response);
                }
            }.bind(this));
        },

        done: function (response) {
            if (response.full_import_done == 1) {
                location.reload();
            }
        },

        importError: function (response) {
            this.isShowImportCounter(false);
            this.message(this.messages.error);
            this.importDone(false);
            this.importCounter(0);
            message({ content: response.error });
        }
    });
});
