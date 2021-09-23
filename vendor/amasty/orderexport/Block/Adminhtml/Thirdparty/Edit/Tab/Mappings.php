<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Block\Adminhtml\Thirdparty\Edit\Tab;

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

class Mappings extends Generic implements TabInterface
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
        return __('Field Mappings');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Field Mappings');
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
        $form->setHtmlIdPrefix('thirdparty_');


        /**
         * FIELDSET: Field Mapping
         */

        $fieldset = $form->addFieldset('mapping_fieldset', ['legend' => __('Field Mapping')]);
        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id', 'value'=>$model->getId()]);
        }

        $name = $fieldset->addField(
            'name', 'text', [
                'name'     => 'name',
                'label'    => __('Name'),
                'title'    => __('Name'),
                'required' => true
            ]
        );

        $tableName = $fieldset->addField(
            'table_name', 'text', [
                'name'     => 'table_name',
                'label'    => __('Table to join'),
                'title'    => __('Table to join'),
                'required' => true
            ]
        );

        $join_field_from = $fieldset->addField(
            'join_field_base', 'text', [
                'name'     => 'join_field_base',
                'label'    => __('Base Table Key'),
                'title'    => __('Base Table Key'),
                'note'     => __('Indicate field from sales_flat_order table based on which the foreign table will be joined (in most cases it is entity_id field).'),
                'required' => true
            ]
        );

        $join_field_from = $fieldset->addField(
            'join_field_reference', 'text', [
                'name'     => 'join_field_reference',
                'label'    => __('Referenced Table Key'),
                'title'    => __('Referenced Table Key'),
                'note'     => __('Field from the foreign table (the one indicated in the `Table to join`), by which the table will be joined to the order table.'),
                'required' => true
            ]
        );

        /*
         * AJAX TABLE FIELDS LOADING
         * for future development & feature enabling
         */

        /*$checkMappingButton = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button',
            '',
            [
                'data' => [
                    'type' => 'button',
                    'id' => 'loadTableFieldButton',
                    'label' => __('Load Table Fields'),
                    'onclick' => 'amOrderexportLoadFields();return false;',
                ]
            ]
        );

        $fld2 = $fieldset->addField('loadTableFields', 'note', ['text' => $checkMappingButton->toHtml()]);

        $mappingOptions = $this
            ->getLayout()
            ->createBlock(
                'Amasty\Orderexport\Block\Adminhtml\Thirdparty\Edit\Options\Map'
            );
        $mappingOptions->setData($model->getData());

        $fld3 = $fieldset->addField('export_mapping_options', 'note', ['text' => $mappingOptions->toHtml()]);
*/


        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
