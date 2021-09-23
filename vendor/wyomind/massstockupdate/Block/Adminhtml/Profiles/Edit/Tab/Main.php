<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Class Main
 * @package Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Edit\Tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    public $module = "MassStockUpdate";
    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    public $fieldFactory;
    /**
     * @var \Magento\Framework\Data\Form\FormKey|null
     */
    public $_formkey = null;
    public function __construct(\Wyomind\MassStockUpdate\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry, $formFactory, $data);
        $this->fieldFactory = $fieldFactory;
        $this->_formkey = $context->getFormKey();
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Settings');
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Settings');
    }
    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('profile');
        $form = $this->_formFactory->create();
        $class = "\\Wyomind\\" . $this->module . "\\Helper\\Data";
        $fieldset = $form->addFieldset($this->module . '_general_settings', ['legend' => __('Import Profile Settings')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        /* FTP */
        $fieldset->addField('enabled', 'select', ['label' => __('Enabled'), 'name' => 'enabled', 'id' => 'enabled', 'required' => true, 'values' => ["1" => __('Yes'), '0' => __('No')]]);
        $fieldset->addField('name', 'text', ['name' => 'name', 'label' => __('Profile name'), 'required' => true]);
        $fieldset->addField('sql', 'select', ['name' => 'sql', 'label' => __('SQL mode'), 'required' => true, 'values' => ['1' => __('Yes'), '0' => __('No')], 'note' => __("When SQL mode is enabled, no stocks are updated. Running the profile will only produce a SQL file. This file could be executed directly in your database manager")]);
        $fieldset->addField('sql_file', 'text', ['name' => 'sql_file', 'label' => __('SQL file name'), 'required' => true, 'note' => __('Name of the SQL file to generate.')]);
        $fieldset->addField('sql_path', 'text', ['name' => 'sql_path', 'label' => __('SQL file path'), 'required' => true, 'note' => __('Path where the SQL file will be generated (relative to Magento root folder).')]);
        if ($this->module == 'MassProductImport') {
            $visibility = 'select';
        } else {
            $visibility = 'hidden';
        }
        $fieldset->addField('profile_method', $visibility, ['name' => 'profile_method', 'value' => $model->getProfileMethod(), 'label' => __('Profile method'), 'options' => [$class::UPDATE => __('Update existing products only'), $class::IMPORT => __('Import new products only'), $class::UPDATEIMPORT => __('Update products and import new products')], "note" => "<script> 
                require(['jquery'],function(\$){
                   \$('#profile_method').on('change',function(){updateImportupdate()});
                   \$(document).ready(function(){updateImportupdate()});
                   function updateImportupdate(){
                        
                        if(\$('#profile_method').val()!=3){
                            \$('#mapping-area').addClass('importupdate-hidden');
                            \$('.importupdate-row').addClass('hidden');
                        }
                        else{
                             \$('#mapping-area').removeClass('importupdate-hidden');
                            \$('.importupdate-row').removeClass('hidden')
                        }
                    }
                }) 
                
                </script>"]);
        $fieldset->addField('line_filter', 'text', ['name' => 'line_filter', 'label' => __('Filter lines'), 'note' => __('<ul><li>Leave empty to import/preview all lines</li>' . '<li>Type the numbers of the lines you want to import<br/>' . '<i>e.g: 5,8  means that only lines number 5 and 8 will be imported</i></li>' . '<li>Use a dash (-) to denote a range of lines<br/>' . '<i>e.g: 8-10 means lines 8,9,10 will be imported</i></li>' . '<li>Use a plus (+) to import all lines from a line number<br/>' . '<i>e.g: 4+ means all lines from line 4 will be imported</i></li>' . '<li> Separate each line or range with a comma (,)<br/>' . '<i>e.g: 2,6-10,15+ means lines 2,6,7,8,9,10,15,16,17,... will be imported</i></li>' . '<li>Use regular expressions surrounded by a # before and after to indicate a particular group of identifiers to import<br/>' . '<i>e.g: #ABC-[0-9]+# all lines with an identifier matching the regular expression will be imported</i></li></ul>'), 'class' => 'updateOnChange']);
        $fieldset = $form->addFieldset($this->module . '_file_location', ['legend' => __('File Location')]);
        //        $session = Mage::getSingleton('core/session');
        //        $SID = $session->getEncryptedSessionId();
        $SID = $this->_sessionManager->getSessionId();
        $formKey = $this->_formkey->getFormKey();
        $fieldset->addField('file_system_type', 'select', ['name' => 'file_system_type', 'label' => __('File location'), 'class' => 'updateOnChange', 'required' => true, 'values' => [$class::LOCATION_MAGENTO => __('Magento file system'), $class::LOCATION_FTP => __('Ftp server'), $class::LOCATION_URL => __('Url'), $class::LOCATION_WEBSERVICE => __('Web service'), $class::LOCATION_DROPBOX => __('Dropbox')], 'note' => " <div id='uploader'>
                            <div id='holder' class='holder'>
                                <div> Drag files from your desktop <br>txt, csv or xml files only</div>
                                <div> " . __("Maximum size") . " " . $this->_dataHelper->getMaxFileSize() . "</div>

                            </div> 

                            <progress id='uploadprogress' max='100' value='0'>0</progress>
                        </div>
                        <script>
                            require(['jquery'],function(\$){
                                \$('#file_system_type').on('change',function(){updateFileSystemType()});
                                \$(document).ready(function(){updateFileSystemType()});
                                function updateFileSystemType(){

                                     if(\$('#file_system_type').val()!=1){
                                         \$('#uploader').css('display','none')
                                     }
                                     else{
                                         \$('#uploader').css('display','block')
                                     }
                                 }
                             })
                             require(['wyomind_uploader_plugin'], function(uploader){
                                 var holder = document.getElementById('holder');
                            var progress = document.getElementById('uploadprogress');
                            var uploadUrl = '" . $this->getUrl('*/*/upload') . "?SID=" . $SID . "';
                            uploader.initialize(holder, progress,uploadUrl,'" . $formKey . "');
                             })
                            
                        </script>"]);
        /* FTP */
        $fieldset->addField('use_sftp', 'select', ['label' => __('Use SFTP'), 'name' => 'use_sftp', 'id' => 'use_sftp', 'class' => 'updateOnChange', 'required' => true, 'values' => ["1" => __('Yes'), '0' => __('No')]]);
        $fieldset->addField('ftp_active', 'select', ['label' => __('Use active mode'), 'name' => 'ftp_active', 'class' => 'updateOnChange', 'id' => 'ftp_active', 'required' => true, 'values' => ["1" => __('Yes'), '0' => __('No')]]);
        $fieldset->addField('ftp_host', 'text', ['label' => __('Host'), 'name' => 'ftp_host', 'class' => 'updateOnChange', 'id' => 'ftp_host']);
        $fieldset->addField('ftp_port', 'text', ['label' => __('Port'), 'name' => 'ftp_port', 'class' => 'updateOnChange', 'id' => 'ftp_port']);
        $fieldset->addField('ftp_login', 'text', ['label' => __('Login'), 'name' => 'ftp_login', 'class' => 'updateOnChange', 'id' => 'ftp_login']);
        $fieldset->addField('ftp_password', 'password', ['label' => __('Password'), 'name' => 'ftp_password', 'class' => 'updateOnChange', 'id' => 'ftp_password']);
        $fieldset->addField('ftp_dir', 'text', ['label' => __('Directory'), 'name' => 'ftp_dir', 'class' => 'updateOnChange', 'id' => 'ftp_dir', 'note' => __("<a style='margin:10px; display:block;' href='javascript:void(require([\"wyomind_MassImportAndUpdate_ftp\"], function (ftp) { ftp.test(\"%1\")}))'>Test Connection</a>", $this->getUrl('*/*/ftp'))]);
        /* Common */
        $fieldset->addField('file_path', 'text', ['name' => 'file_path', 'class' => 'updateOnChange', 'label' => __('File Path'), 'required' => true, 'note' => __("- <b>Magento file system</b> : File path relative to Magento root folder</i><br/>") . __("- <b>FTP server</b> : File path relative to ftp user root folder<br/>") . __("- <b>URL</b> : Url of the file<br/>") . __("- <b>Web service</b> : Url of the web service<br/>") . __("- <b>Dropbox</b> : Url of the dropbox service<br/>")]);
        /* Dropbox */
        $fieldset->addField('dropbox_token', 'text', ['name' => 'dropbox_token', 'class' => 'updateOnChange', 'label' => __('Access token'), 'required' => false, 'note' => __("You can generate your token from your Dropbox account https://www.dropbox.com/developers/apps")]);
        /* Web service */
        $fieldset->addField('webservice_params', 'textarea', ['label' => __('Parameters'), 'name' => 'webservice_params', 'class' => 'updateOnChange', 'id' => 'webservice_params']);
        $fieldset->addField('webservice_login', 'text', ['label' => __('Login'), 'class' => 'updateOnChange', 'name' => 'webservice_login', 'id' => 'webservice_login']);
        $fieldset->addField('webservice_password', 'password', ['label' => __('Password'), 'class' => 'updateOnChange', 'name' => 'webservice_password', 'id' => 'webservice_password']);
        $fieldset->addField('file_is_valid', 'hidden', ['name' => 'file_is_valid', 'id' => 'file_is_valid']);
        $configUrl = $this->getUrl('adminhtml/system_config/edit', ['section' => strtolower($this->module)]);
        $fieldset = $form->addFieldset($this->module . '_file_type', ['legend' => __('File Type')]);
        $fieldset->addField('file_type', 'select', ['name' => 'file_type', 'class' => 'updateOnChange', 'label' => __('File type'), 'required' => true, 'values' => [$class::CSV => __('CSV'), $class::XML => __('XML'), $class::JSON => __('JSON')]]);
        /* CSV */
        $fieldset->addField('field_delimiter', 'select', ['name' => 'field_delimiter', 'class' => 'updateOnChange', 'label' => __('Column separator'), 'values' => $this->_dataHelper->getFieldDelimiters()]);
        $fieldset->addField('field_enclosure', 'select', ['name' => 'field_enclosure', 'class' => 'updateOnChange', 'label' => __('Text delimiter'), 'values' => $this->_dataHelper->getFieldEnclosures()]);
        $fieldset->addField('has_header', 'select', ['name' => 'has_header', 'label' => __('The first line is a header'), 'options' => [1 => 'Yes', 0 => 'No'], 'class' => 'updateOnChange']);
        $fieldset->addField('is_magento_export', 'select', ['name' => 'is_magento_export', 'class' => 'updateOnChange', 'label' => __('Magento export file'), 'required' => true, 'values' => [$class::IS_MAGENTO_EXPORT_YES => __('Yes'), $class::IS_MAGENTO_EXPORT_NO => __('No')], 'note' => __("Magento default export files are made of empty rows with values related to main rows, activating this option will merge the values into the main rows")]);
        /* XML */
        $fieldset->addField('xml_xpath_to_product', 'text', ['name' => 'xml_xpath_to_product', 'class' => 'updateOnChange', 'label' => __('Path to products'), 'required' => true, 'note' => __("Path where the product data is stored in the source file<br> Use Xpath for XML files, e.g.:/catalog/products/product<br>Use JSONPath for JSON files, e.g.:\$catalog.products.product")]);
        $fieldset->addField('preserve_xml_column_mapping', 'select', ['label' => __('Data structure'), 'name' => 'preserve_xml_column_mapping', 'class' => 'updateOnChange', 'id' => 'preserve_xml_column_mapping', 'required' => true, 'values' => ['1' => __('Predefined structure'), '0' => __('Automatic detection')], 'note' => __("The automatic detection of the file structure fits for simple files made of only one nesting level. ")]);
        $fieldset->addField('xml_column_mapping', 'textarea', ['label' => __('Predefined structure'), 'name' => 'xml_column_mapping', 'class' => "updateOnChange hidden", 'id' => 'xml_column_mapping', 'note' => __("The predefined structure must be a valid Json string made of a key/value list that define the column names and the Xpath/JSONPath associated to the column")]);
        $fieldset->addField('run', 'hidden', ['name' => 'run', 'class' => 'debug', 'value' => '']);
        $fieldset->addField('run_i', 'hidden', ['name' => 'run_i', 'value' => '']);
        $fieldset = $form->addFieldset($this->module . '_post_process', ['legend' => __('Post Process Action')]);
        $fieldset->addField('post_process_action', 'select', ['label' => __('Action on import file'), 'name' => 'post_process_action', 'id' => 'post_process_action', 'required' => true, 'values' => [$class::POST_PROCESS_ACTION_NOTHING => __('Do Nothing'), $class::POST_PROCESS_ACTION_DELETE => __('Delete the import file'), $class::POST_PROCESS_ACTION_MOVE => __('Move import file')]]);
        $fieldset->addField('post_process_move_folder', 'text', ['label' => __('Move to folder'), 'name' => 'post_process_move_folder', 'id' => 'post_process_move_folder', 'required' => true, 'note' => "File path relative to the root folder"]);
        $fieldset->addField('post_process_indexers', 'select', ['label' => __('Run indexers'), 'name' => 'post_process_indexers', 'id' => 'post_process_indexers', 'required' => true, 'values' => [$class::POST_PROCESS_INDEXERS_DISABLED => __('No'), $class::POST_PROCESS_INDEXERS_AUTOMATICALLY => __('Only the required indexers'), $class::POST_PROCESS_INDEXERS_ONLY_SELECTED => __('Only the selected indexers')], "note" => __("The indexes may need to be updated after importing data") . "<br/>" . "- <b>" . __("No") . "</b>: " . __("no indexer will run and will have to reindex manually from the CLI") . "<br/>" . "- <b>" . __("Only the required indexers") . "</b>: " . __("automatically decides which indexers must run") . "<br/>" . "- <b>" . __("Only the selected indexers") . "</b>: " . __("let you decide which indexers to run") . "<br/>"]);
        $indexes = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $dataHelperClass = get_class($this->_dataHelper);
        foreach ($dataHelperClass::MODULES as $module) {
            $resource = $objectManager->create("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\" . $module);
            $columns = json_decode($model->getMapping());
            if ($columns === null) {
                $columns = [];
            }
            $indexes = array_unique($indexes + $resource->getIndexes($columns));
        }
        $indexersList = [];
        foreach ($indexes as $sort => $name) {
            $indexersList[] = ["label" => "{$name}", "value" => "{$name}"];
        }
        $fieldset->addField('post_process_indexers_selection', 'multiselect', ['label' => __('Indexer to run'), 'name' => 'post_process_indexers_selection', 'id' => 'post_process_indexers_selection', 'required' => true, 'values' => $indexersList]);
        $fieldset->addField('identifier_offset', 'hidden', ['name' => 'identifier_offset']);
        $fieldset->addField('identifier', 'hidden', ['name' => 'identifier']);
        $fieldset->addField('mapping', 'hidden', ['name' => 'mapping']);
        $block = $this->getLayout()->createBlock('Wyomind\\Framework\\Block\\Adminhtml\\Backend\\Widget\\Form\\Element\\Dependence');
        // "Action on import file" dependencies
        $postProcessActionDependencies = $this->fieldFactory->create(['fieldData' => ['value' => $class::LOCATION_MAGENTO . ',' . $class::LOCATION_FTP, 'separator' => ','], 'fieldPrefix' => '']);
        $this->setChild('form_after', $block->addFieldMap('file_system_type', 'file_system_type')->addFieldMap('file_type', 'file_type')->addFieldMap('create_category_onthefly', 'create_category_onthefly')->addFieldMap('category_is_active', 'category_is_active')->addFieldMap('category_include_in_menu', 'category_include_in_menu')->addFieldMap('category_parent_id', 'category_parent_id')->addFieldDependence('category_is_active', 'create_category_onthefly', 1)->addFieldDependence('category_include_in_menu', 'create_category_onthefly', 1)->addFieldDependence('category_parent_id', 'create_category_onthefly', 1)->addFieldMap('images_system_type', 'images_system_type')->addFieldMap('images_use_sftp', 'images_use_sftp')->addFieldMap('images_ftp_host', 'images_ftp_host')->addFieldMap('images_ftp_port', 'images_ftp_port')->addFieldMap('images_ftp_login', 'images_ftp_login')->addFieldMap('images_ftp_password', 'images_ftp_password')->addFieldMap('images_ftp_active', 'images_ftp_active')->addFieldDependence('images_ftp_port', 'images_system_type', 1)->addFieldDependence('images_ftp_host', 'images_system_type', 1)->addFieldDependence('images_use_sftp', 'images_system_type', 1)->addFieldDependence('images_ftp_login', 'images_system_type', 1)->addFieldDependence('images_ftp_password', 'images_system_type', 1)->addFieldDependence('images_ftp_active', 'images_system_type', 1)->addFieldDependence('images_ftp_active', 'images_use_sftp', 0)->addFieldMap('sql', 'sql')->addFieldMap('sql_file', 'sql_file')->addFieldMap('sql_path', 'sql_path')->addFieldDependence('sql_file', 'sql', $class::YES)->addFieldDependence('sql_path', 'sql', $class::YES)->addFieldMap('use_sftp', 'use_sftp')->addFieldMap('ftp_host', 'ftp_host')->addFieldMap('ftp_login', 'ftp_login')->addFieldMap('ftp_password', 'ftp_password')->addFieldMap('ftp_dir', 'ftp_dir')->addFieldMap('ftp_port', 'ftp_port')->addFieldMap('ftp_active', 'ftp_active')->addFieldDependence('ftp_host', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('use_sftp', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_login', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_password', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_active', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_dir', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_port', 'file_system_type', $class::LOCATION_FTP)->addFieldDependence('ftp_active', 'use_sftp', $class::NO)->addFieldMap('dropbox_token', 'dropbox_token')->addFieldDependence('dropbox_token', 'file_system_type', $class::LOCATION_DROPBOX)->addFieldMap('webservice_params', 'webservice_params')->addFieldMap('webservice_login', 'webservice_login')->addFieldMap('webservice_password', 'webservice_password')->addFieldDependence('webservice_params', 'file_system_type', $class::LOCATION_WEBSERVICE)->addFieldDependence('webservice_login', 'file_system_type', $class::LOCATION_WEBSERVICE)->addFieldDependence('webservice_password', 'file_system_type', $class::LOCATION_WEBSERVICE)->addFieldMap('use_custom_rules', 'use_custom_rules')->addFieldMap('custom_rules', 'custom_rules')->addFieldDependence('custom_rules', 'use_custom_rules', $class::YES)->addFieldMap('field_delimiter', 'field_delimiter')->addFieldMap('has_header', 'has_header')->addFieldMap('field_enclosure', 'field_enclosure')->addFieldMap('is_magento_export', 'is_magento_export')->addFieldDependence('field_enclosure', 'file_type', $class::CSV)->addFieldDependence('field_delimiter', 'file_type', $class::CSV)->addFieldDependence('has_header', 'file_type', $class::CSV)->addFieldDependence('is_magento_export', 'file_type', $class::CSV)->addFieldMap('xml_xpath_to_product', 'xml_xpath_to_product')->addFieldMap('xml_column_mapping', 'xml_column_mapping')->addFieldMap('preserve_xml_column_mapping', 'preserve_xml_column_mapping')->addFieldDependence('xml_xpath_to_product', 'file_type', [$class::XML, $class::JSON])->addFieldDependence('preserve_xml_column_mapping', 'file_type', [$class::XML, $class::JSON])->addFieldDependence('xml_column_mapping', 'file_type', [$class::XML, $class::JSON])->addFieldDependence('xml_column_mapping', 'preserve_xml_column_mapping', 1)->addFieldMap('post_process_action', 'post_process_action')->addFieldMap('post_process_move_folder', 'post_process_move_folder')->addFieldMap('post_process_indexers', 'post_process_indexers')->addFieldMap('post_process_indexers_selection', 'post_process_indexers_selection')->addFieldDependence('post_process_action', 'file_system_type', $postProcessActionDependencies)->addFieldDependence('post_process_move_folder', 'file_system_type', $postProcessActionDependencies)->addFieldDependence('post_process_move_folder', 'post_process_action', $class::POST_PROCESS_ACTION_MOVE)->addFieldDependence('post_process_indexers_selection', 'post_process_indexers', $class::POST_PROCESS_INDEXERS_ONLY_SELECTED));
        $model->setFileIsValid(0);
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}