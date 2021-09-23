<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Block\Adminhtml\Profiles\Edit\Tab;

use Amasty\Orderexport\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

class File extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var Data
     */
    protected $_groupManagement;

    /**
     * @var \Amasty\Orderexport\Helper\Data
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @param Context                  $context
     * @param Registry                 $registry
     * @param FormFactory              $formFactory
     * @param GroupManagementInterface $groupManagement
     * @param ObjectConverter          $objectConverter
     * @param Store                    $systemStore
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param Data                     $helper
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        GroupManagementInterface $groupManagement,
        ObjectConverter $objectConverter,
        Store $systemStore,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Data $helper,
        array $data = []
    ) {
        $this->_groupManagement       = $groupManagement;
        $this->_helper                = $helper;
        $this->_systemStore           = $systemStore;
        $this->_objectConverter       = $objectConverter;
        $this->groupRepository        = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Export File Options');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Export File Options');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amasty_orderexport');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('profile_');


        $fileProperties = $form->addFieldset('file_fieldset', ['legend' => __('File Properties')]);

        $fileProperties->addField('filename', 'text', [
            'name'     => 'filename',
            'label'    => __('File Name'),
            'title'    => __('File Name'),
            'required' => true,
            'note'     => __('Just name, with no extension. Will be used both for file saved to local folder and for one uploaded via FTP.'."\r\n"
                            .'<b>NOTE:</b> if "Add Timestamp" is disabled, file will be overwritten on each export run!'),
        ]);

        $fileProperties->addField('path', 'text', [
            'name'  => 'path',
            'label' => __('File Path (Local)'),
            'title' => __('File Path (Local)'),
            'required' => true,
            'note'  => __('Absolute path, or relative to Magento install root, ex. "var/export/". Please make sure that this directory exists and is writeable.'),
        ]);

        $fileProperties->addField('export_add_timestamp', 'select', [
            'name'   => 'export_add_timestamp',
            'label'  => __('Add Timestamp'),
            'title'  => __('Add Timestamp'),
            'note'   => __('Timestamp will be added as a prefix to the file or as a new folder in which file will be saved'),
            'values' => [
                [
                    'value' => '0',
                    'label' => __('Do not add')
                ],
                [
                    'value' => '1',
                    'label' => __('Add timestamp to file name as a prefix')
                ],
                [
                    'value' => '2',
                    'label' => __('Create new folder and place file in it')
                ],
            ],
        ]);

//        $fileProperties->addField('post_date_format', 'text', [
//            'name'  => 'post_date_format',
//            'label' => __('Date Format'),
//            'title' => __('Date Format'),
//            'note'  => __('Convert all dates to the specified format (according to the php <a href="http://php.net/manual/en/function.date.php" target="_blank">date()</a> function format). Leave empty for no post-processing.')
//                       . '<br />'
//                       . __('For example use <strong>d/m/Y</strong> to get ') . date('d/m/Y'),
//        ]);


        /**
         * FIELDSET: Data Format
         */

        $fieldsetFormat = $form->addFieldset('data_fieldset', ['legend' => __('Data Format')]);

        $fel1 = $fieldsetFormat->addField('format', 'select', [
            'name'     => 'format',
            'label'    => __('File Format'),
            'title'    => __('File Format'),
            'values'   => [
                [
                    'value' => '0',
                    'label' => __('CSV - Comma Separated Values'),
                ],
                [
                    'value' => '2',
                    'label' => __('XML'),
                ],
                [
                    'value' => '1',
                    'label' => __('MS Excel XML'),
                ],

            ]
        ]);

        $fel2 = $fieldsetFormat->addField('export_include_fieldnames', 'select', [
            'name'    => 'export_include_fieldnames',
            'label'   => __('Field Names In The First Row'),
            'title'   => __('Field Names In The First Row'),
            'value'   => 1,
            'options' => [
                1 => __('Yes'),
                0 => __('No'),
            ],
        ]);

        $fel3 = $fieldsetFormat->addField('split_order_items', 'select', [
            'name'    => 'split_order_items',
            'label'   => __('Split Order Items'),
            'title'   => __('Split Order Items'),
            'value'   => 1,
            'options' => [
                1 => __('Yes'),
                0 => __('No'),
            ],
        ]);

        $fel4 = $fieldsetFormat->addField('split_order_items_delim', 'text', [
            'name'  => 'split_order_items_delim',
            'label' => __('Order Items Delimiter'),
            'title' => __('Order Items Delimiter'),
        ]);

        /**
         * FIELDSET: CSV Configuration
         */

        $fieldset = $form->addFieldset('csv_fieldset', ['legend' => __('CSV Configuration')]);

        $cel1 = $fieldset->addField('csv_delim', 'text', [
            'name'  => 'csv_delim',
            'label' => __('Delimiter'),
            'title' => __('Delimiter'),
        ]);

        $cel2 = $fieldset->addField('csv_enclose', 'text', [
            'name'  => 'csv_enclose',
            'label' => __('Enclose Values In'),
            'title' => __('Enclose Values In'),
            'note'  => __('Warning! Empty value can cause problems with CSV format.'),
        ]);

        /**
         * FIELDSET: XML Configuration
         */

        $fieldset = $form->addFieldset('xml_fieldset', ['legend' => __('XML Configuration')]);

        $xel1 = $fieldset->addField('xml_main_tag', 'text', [
            'name'  => 'xml_main_tag',
            'label' => __('XML Main Tag'),
            'title' => __('XML Main Tag'),
        ]);

        $xel2 = $fieldset->addField('xml_order_tag', 'text', [
            'name'  => 'xml_order_tag',
            'label' => __('XML Order Tag'),
            'title' => __('XML Order Tag'),
        ]);

        $xel3 = $fieldset->addField('xml_order_items_tag', 'text', [
            'name'  => 'xml_order_items_tag',
            'label' => __('XML Order Items Tag'),
            'title' => __('XML Order Items Tag'),
        ]);

        $xel4 = $fieldset->addField('xml_order_item_tag', 'text', [
            'name'  => 'xml_order_item_tag',
            'label' => __('XML Order Item Tag'),
            'title' => __('XML Order Item Tag'),
        ]);

        /**
         * FIELDSET: Data Format
         */

        $fieldset = $form->addFieldset('ftp_fieldset', ['legend' => __('FTP/SFTP Configuration')]);

        $sel1 = $fieldset->addField('ftp_use', 'select', [
            'name'     => 'ftp_use',
            'label'    => __('Upload Exported File By FTP'),
            'title'    => __('Upload Exported File By FTP'),
            'values'   => [
                '0' => __('No'),
                '1' => __('Yes'),
            ]
        ]);

        $sel2 = $fieldset->addField('ftp_host', 'text', [
            'name'  => 'ftp_host',
            'label' => __('FTP Hostname'),
            'title' => __('FTP Hostname'),
            'note'  => __('If you use non-standard port (not 21), please specify hostname like example.com:23, where 23 is your custom port'),
        ]);
        $sel3 = $fieldset->addField('type', 'select', [
            'name'     => 'type',
            'label'    => __('Type'),
            'title'    => __('Type'),
            'values'   => [
                [
                    'value' => '0',
                    'label' => __('FTP'),
                ],
                [
                    'value' => '1',
                    'label' => __('SFTP'),
                ],
            ],
        ]);

        $sel4 = $fieldset->addField('ftp_login', 'text', [
            'name'  => 'ftp_login',
            'label' => __('FTP Login'),
            'title' => __('FTP Login'),
        ]);

        $sel5 = $fieldset->addField('ftp_password', 'text', [
            'name'  => 'ftp_password',
            'label' => __('FTP Password'),
            'title' => __('FTP Password'),
        ]);

        $sel6 = $fieldset->addField('ftp_is_passive', 'select', [
            'name'   => 'ftp_is_passive',
            'label'  => __('Use Passive Mode'),
            'title'  => __('Use Passive Mode'),
            'values' => [
                '0' => __('No'),
                '1' => __('Yes'),
            ],
        ]);

        $sel7 = $fieldset->addField('ftp_path', 'text', [
            'name'  => 'ftp_path',
            'label' => __('File Path (FTP)'),
            'title' => __('File Path (FTP)'),
        ]);

        $sel8 = $fieldset->addField('ftp_delete_local', 'select', [
            'name'   => 'ftp_delete_local',
            'label'  => __('Delete Local File After FTP Upload'),
            'title'  => __('Delete Local File After FTP Upload'),
            'values' => [
                '0' => __('No'),
                '1' => __('Yes'),
            ],
        ]);


        /**
         * FIELDSET: E-mail Settings
         */
        $fieldset = $form->addFieldset('email_fieldset', ['legend' => __('E-mail Settings')]);

        $eel1 = $fieldset->addField('email_use', 'select', [
            'name'     => 'email_use',
            'label'    => __('Send Exported File to E-mail'),
            'title'    => __('Send Exported File to E-mail'),
            'values'   => [
                '0' => __('No'),
                '1' => __('Yes'),
            ]
        ]);

        $eel5 = $fieldset->addField('email_from', 'select', [
            'name'  => 'email_from',
            'label' => __('E-mail From'),
            'title' => __('E-mail From'),
            'values'   => $this->_helper->getEmails(),
        ]);

        $eel2 = $fieldset->addField('email_address', 'text', [
            'name'  => 'email_address',
            'label' => __('E-mail Address'),
            'title' => __('E-mail Address'),
        ]);

        $eel3 = $fieldset->addField('email_subject', 'text', [
            'name'  => 'email_subject',
            'label' => __('E-mail Message Subject'),
            'title' => __('E-mail Message Subject'),
        ]);

        $eel4 = $fieldset->addField('email_compress', 'select', [
            'name'   => 'email_compress',
            'label'  => __('Compress Exported File in ZIP'),
            'title'  => __('Compress Exported File in ZIP'),
            'values' => [
                '0' => __('No'),
                '1' => __('Yes'),
            ],
        ]);

        /**@var \Magento\Backend\Block\Widget\Form\Element\Dependence $dependence */
        $dependence = $this
            ->getLayout()
            ->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            );
        $dependence
            ->addFieldMap($fel1->getHtmlId(), $fel1->getName())
            ->addFieldMap($fel2->getHtmlId(), $fel2->getName())
            ->addFieldMap($fel3->getHtmlId(), $fel3->getName())
            ->addFieldMap($fel4->getHtmlId(), $fel4->getName())
            ->addFieldMap($xel1->getHtmlId(), $xel1->getName())
            ->addFieldMap($xel2->getHtmlId(), $xel2->getName())
            ->addFieldMap($xel3->getHtmlId(), $xel3->getName())
            ->addFieldMap($xel4->getHtmlId(), $xel4->getName())
            ->addFieldMap($cel1->getHtmlId(), $cel1->getName())
            ->addFieldMap($cel2->getHtmlId(), $cel2->getName())
            ->addFieldDependence(
                $cel1->getName(),
                $fel1->getName(),
                '0'
            )
            ->addFieldDependence(
                $cel2->getName(),
                $fel1->getName(),
                '0'
            )
            ->addFieldDependence(
                $fel4->getName(),
                $fel3->getName(),
                '1'
            )->addFieldDependence(
                $xel1->getName(),
                $fel1->getName(),
                '2'
            )->addFieldDependence(
                $xel2->getName(),
                $fel1->getName(),
                '2'
            )->addFieldDependence(
                $xel3->getName(),
                $fel1->getName(),
                '2'
            )->addFieldDependence(
                $xel4->getName(),
                $fel1->getName(),
                '2'
            )
            ->addFieldMap($sel1->getHtmlId(), $sel1->getName())
            ->addFieldMap($sel2->getHtmlId(), $sel2->getName())
            ->addFieldMap($sel3->getHtmlId(), $sel3->getName())
            ->addFieldMap($sel4->getHtmlId(), $sel4->getName())
            ->addFieldMap($sel5->getHtmlId(), $sel5->getName())
            ->addFieldMap($sel6->getHtmlId(), $sel6->getName())
            ->addFieldMap($sel7->getHtmlId(), $sel7->getName())
            ->addFieldMap($sel8->getHtmlId(), $sel8->getName())
            ->addFieldDependence(
                $sel2->getName(),
                $sel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $sel3->getName(),
                $sel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $sel4->getName(),
                $sel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $sel5->getName(),
                $sel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $sel6->getName(),
                $sel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $sel7->getName(),
                $sel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $sel8->getName(),
                $sel1->getName(),
                '1'
            )
            ->addFieldMap($eel1->getHtmlId(), $eel1->getName())
            ->addFieldMap($eel5->getHtmlId(), $eel5->getName())
            ->addFieldMap($eel2->getHtmlId(), $eel2->getName())
            ->addFieldMap($eel3->getHtmlId(), $eel3->getName())
            ->addFieldMap($eel4->getHtmlId(), $eel4->getName())
            ->addFieldDependence(
                $eel5->getName(),
                $eel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $eel2->getName(),
                $eel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $eel3->getName(),
                $eel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $eel4->getName(),
                $eel1->getName(),
                '1'
            );
        $this->setChild('form_after', $dependence);

        $form->setValues($model->getData());

        if (!$model->getId()) {
            $form->addValues([
                'filename' => 'exported_orders',
                'path' => 'var/export/',
                'split_order_items' => '1',
                'split_order_items_delim' => ',',
                'xml_main_tag' => 'orders',
                'xml_order_tag' => 'order',
                'xml_order_items_tag' => 'order_items',
                'xml_order_item_tag' => 'order_item',
                'csv_delim' => ',',
                'csv_enclose' => '"',
                'ftp_use' => '0',
                'run_after_order_creation' => '0',
                'run_by_cron' => '0',
                'export_include_fieldnames' => '1',
            ]);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
