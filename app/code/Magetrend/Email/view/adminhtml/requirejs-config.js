/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */
var config = {
    map: {
        '*': {
            mtemail_button: 'Magetrend_Email/js/system/config/button',
            mtEditor_jquery: 'Magetrend_Email/js/mteditor/jquery-2.1.3',
            mtEditor_bootstrap: 'Magetrend_Email/js/mteditor/bootstrap.min',
            mtEditor_cookie: 'Magetrend_Email/js/mteditor/jquery.cookie',
            mtEditor_jquery_ui: 'Magetrend_Email/js/mteditor/jquery-ui',
            mtEditor_ui_widget: 'Magetrend_Email/js/mteditor/jquery.ui.widget',
            mtEditor_iframe_transport: 'Magetrend_Email/js/mteditor/jquery.iframe-transport',
            mtEditor_file_upload: 'Magetrend_Email/js/mteditor/jquery.fileupload',
            mtEditor_helper: 'Magetrend_Email/js/mteditor/text_edit_helper',
            mtEditor_color_picker: 'Magetrend_Email/js/mteditor/colorpicker',
            mtEditor_popup: 'Magetrend_Email/js/mteditor/popup',
            mtEditor_save_helper: 'Magetrend_Email/js/mteditor/helper/save',
            mtEditor_metis_menu: 'Magetrend_Email/js/mteditor/jquery.metisMenu',
            mtEditor_editor: 'Magetrend_Email/js/mteditor/editor',
            emailImportForm: 'Magetrend_Email/js/import',
        },
        shim: {
            'mtEditor_bootstrap': {
                deps: ['jquery']
            },
            'mtEditor_cookie': {
                deps: ['jquery']
            },
            'mtEditor_jquery_ui': {
                deps: ['jquery']
            },
            'mtEditor_ui_widget': {
                deps: ['jquery']
            },
            'mtEditor_iframe_transport': {
                deps: ['jquery']
            },
            'mtEditor_file_upload': {
                deps: ['jquery']
            },
            'mtEditor_helper': {
                deps: ['jquery']
            },
            'mtEditor_color_picker': {
                deps: ['jquery']
            },
            'mtEditor_popup': {
                deps: ['jquery']
            },
            'mtEditor_save_helper': {
                deps: ['jquery']
            },
            'mtEditor_metis_menu': {
                deps: ['jquery']
            },
            'mtEditor_editor': {
                deps: ['jquery']
            }
        }
    }
};