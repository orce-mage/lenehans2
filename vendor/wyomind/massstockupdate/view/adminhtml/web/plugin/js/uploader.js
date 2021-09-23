/*
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([], function () {
    "use strict";
    return {
        tests: {
            filereader: typeof FileReader != 'undefined',
            dnd: 'draggable' in document.createElement('span'),
            formdata: !!window.FormData,
            progress: "upload" in new XMLHttpRequest
        },
        acceptedTypes: {
            'text/plain': true,
            '': true,
            'text/xml': true
        },
        xhr: [],
        readfiles: function (files, progress, url, key) {

            var formData = this.tests.formdata ? new FormData() : null;
            if (files.length > 1) {
                alert("Multiple files upload is not supported.");
                return;
            }

            for (var i = 0; i < files.length; i++) {

//            if (this.acceptedTypes[files[i].type] !== true) {
//                alert("Wrong file type. Xml, csv or txt files only.\n" + files[i].name + " can't be uploaded.");
//            } else {
                if (this.tests.formdata) {

                    formData.append('file_upload', files[i]);
                    formData.append('form_key', key);
                    this.xhr[i] = new XMLHttpRequest();

                    this.xhr[i].open('POST', url);

                    if (this.tests.progress) {
                        this.xhr[i].upload.onprogress = function (event) {
                            if (event.lengthComputable) {
                                var complete = (event.loaded / event.total * 100 | 0);
                                progress.value = progress.innerHTML = complete;
                            }
                        };
                        this.xhr[i].onload = function (response) {

                            try {
                                progress.value = progress.innerHTML = 0;
                                var r = (this.response.evalJSON())
                                if (r.error === true) {
                                    alert(r.message);
                                } else {

                                    document.getElementById("file_path").value = r.message;

                                }
                            } catch (err) {
                                alert("Error:" + err.message);
                            }
                        }
                    }

                    this.xhr[i].send(formData);
                }


//            }

            }

        },
        initialize: function (holder, progress, url, key) {


            if (this.tests.dnd) {
                holder.ondragover = function () {
                    this.addClassName('hover');
                    return false;
                };
                holder.ondragend = function () {
                    this.removeClassName('hover');
                    return false;

                };
                holder.ondrop = function (e) {
                    this.removeClassName('hover');
                    e.preventDefault();
                    require(['wyomind_uploader_plugin'], function (uploader) {
                        uploader.readfiles(this, progress, url, key);
                    }.bind(e.dataTransfer.files))
                };
            }
        }
    };


})




