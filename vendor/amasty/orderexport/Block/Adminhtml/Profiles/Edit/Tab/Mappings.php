<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Block\Adminhtml\Profiles\Edit\Tab;

use Amasty\Orderexport\Block\Adminhtml\Profiles\Edit\Options\Mapping;
use Amasty\Orderexport\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
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
        $form->setHtmlIdPrefix('profile_');

        /**
         * FIELDSET: Field Mapping
         */

        $fieldset = $form->addFieldset('mapping_fieldset', ['legend' => __('Field Mapping')]);

        $fieldset->addField('export_custom_options', 'select', [
            'name'   => 'export_custom_options',
            'label'  => __('Export each product custom option in a separate column'),
            'title'  => __('Export each product custom option in a separate column'),
            'note'   => __('You need to select "product.product_options" in the list of fields'.
                ' to export custom options selected'),
            'values' => [
                '0' => __('No'),
                '1' => __('Yes'),
            ],
        ]);

        /**
         * @TODO
         * options are hidden because they are unused!
         * for future module upgrade & improvement
         */
//
//        $fieldset->addField('export_attributes_info', 'select', [
//            'name'   => 'export_attributes_info',
//            'label'  => __('Export each product attribute in a separate column'),
//            'title'  => __('Export each product attribute in a separate column'),
//            'note'   => __('You need to select "product.product_options" in the list of'.
//              ' fields to export attributes info'),
//            'values' => [
//                '0' => __('No'),
//                '1' => __('Yes'),
//            ],
//        ]);

        $fld1             = $fieldset->addField('export_allfields', 'select', [
            'name'     => 'export_allfields',
            'label'    => __('Fields To Export'),
            'title'    => __('Fields To Export'),
            'values'   => [
                [
                    'value' => '0',
                    'label' => __('Export All Fields'),
                ],
                [
                    'value' => '1',
                    'label' => __('Export Specified Fields Only'),
                ],
            ],
            'onchange' => 'javascript: checkMapping(this,0);',
            'style'    => "margin-right: 20px;",
        ]);

//        $checkMappingButton = $this->getLayout()->createBlock(
//            'Magento\Backend\Block\Widget\Button',
//            '',
//            [
//                'data' => [
//                    'type' => 'button',
//                    'label' => __('Add Field Mapping'),
//                    'onclick' => 'amAddMapping();return false;',
//                ]
//            ]
//        );
//
//        $fld2 = $fieldset->addField('export_allfields_button', 'note', ['text' => $checkMappingButton->toHtml()]);

        $mappingOptions = $this
            ->getLayout()
            ->createBlock(
                Mapping::class
            );
        $mappingOptions->setData($model->getData());

        $fld3 = $fieldset->addField('export_mapping_options', 'note', ['text' => $mappingOptions->toHtml()]);
        
        // define field dependencies
        /**
         * @var Dependence
         */
        $dependence = $this
            ->getLayout()
            ->createBlock(
                Dependence::class
            )
            // Customer Groups
            ->addFieldMap($fld1->getHtmlId(), $fld1->getName())
            ->addFieldMap($fld3->getHtmlId(), $fld3->getName())
//            ->addFieldDependence(
//                $fld2->getName(),
//                $fld1->getName(),
//                '1'
//            )
            ->addFieldDependence(
                $fld3->getName(),
                $fld1->getName(),
                '1'
            );
        $this->setChild('form_after', $dependence);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
