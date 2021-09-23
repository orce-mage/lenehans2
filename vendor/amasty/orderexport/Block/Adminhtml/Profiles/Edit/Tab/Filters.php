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

class Filters extends Generic implements TabInterface
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
    protected $_groupRepository;

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
        $this->_groupRepository       = $groupRepository;
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
        return __('Orders Filters');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Orders Filters');
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
         * FIELDSET: Filters by order number
         */

        $fieldset = $form->addFieldset('filters_order_number_fieldset', ['legend' => __('Order Number Filters')]);

        $oel1 = $fieldset->addField(
            'filter_number_enabled', 'select', [
                'label'   => __('Use Order Number Filters'),
                'title'   => __('Use Order Number Filters'),
                'name'    => 'filter_number_enabled',
                'options' => [
                    '0' => __('No'),
                    '1' => __('Yes'),
                ],
            ]
        );

        $nextIncrementId  = $this->_helper->getNextIncrementId($model->getLastIncrementId());
        $afterElementHtml = $model->getLastIncrementId() ? __('Last Order Exported: %s <br />', $model->getLastIncrementId()) : '';
        $afterElementHtml .= $nextIncrementId ? 'Next Order Number: ' . $nextIncrementId . '  <br />' : '';
        $oel2 = $fieldset->addField('filter_number_from', 'text', [
            'name'  => 'filter_number_from',
            'label' => __('Starting From #'),
            'title' => __('Starting From #'),
            'note'  => __($afterElementHtml . 'Order number to start export from. Ex. 100000040. Leave empty to ignore.'),
        ]);

        $oel3 = $fieldset->addField('filter_number_to', 'text', [
            'name'  => 'filter_number_to',
            'label' => __('Ending With #'),
            'title' => __('Ending With #'),
            'note'  => __('Order number to end export with. Leave empty to ignore.'),
        ]);

        $oel4 = $fieldset->addField('filter_number_from_skip', 'select', [
            'name'   => 'filter_number_from_skip',
            'label'  => __('Skip Starting From'),
            'title'  => __('Skip Starting From'),
            'note'   => __('In case of "Yes" export will start from the order, next to the specified in the "Starting From #" field. Else specified order will be exported as well.'),
            'values' => [
                '0' => __('No'),
                '1' => __('Yes'),
            ],
        ]);

        $oel5 = $fieldset->addField('increment_auto', 'select', [
            'name'   => 'increment_auto',
            'label'  => __('Automatically Increment Starting From'),
            'title'  => __('Automatically Increment Starting From'),
            'note'   => __('Automatically fill "Starting From #" field with the last exported order number after each profile run'),
            'values' => [
                '0' => __('No'),
                '1' => __('Yes'),
            ],
        ]);


        /**
         * FIELDSET: Filters by order number
         */

        $fieldset = $form->addFieldset('filters_invoice_fieldset', ['legend' => __('Invoice Number Filters')]);

        $iel1 = $fieldset->addField(
            'filter_invoice_enabled', 'select', [
                'label'   => __('Use Invoice NumberFilter'),
                'title'   => __('Use Invoice Number Filter'),
                'name'    => 'filter_invoice_enabled',
                'options' => [
                    '0' => __('No'),
                    '1' => __('Yes'),
                ],
            ]
        );

        $nextInvIncId     = $this->_helper->getNextInvoiceIncrementId($model->getLastInvoiceIncrementId());
        $afterElementHtml = $model->getLastInvoiceIncrementId() ? __('Last Invoice Exported: %s  <br />', $model->getLastInvoiceIncrementId()) : '';
        $afterElementHtml .= $nextInvIncId ? 'Next Invoice Number: ' . $nextInvIncId . ' <br />' : '';
        $iel2 = $fieldset->addField('filter_invoice_from', 'text', [
            'name'  => 'filter_invoice_from',
            'label' => __('Starting From #'),
            'title' => __('Starting From #'),
            'note'  => __($afterElementHtml . 'Invoice number to start export from. Ex. 100000040. Leave empty to ignore.'),
        ]);

        $iel3 = $fieldset->addField('filter_invoice_to', 'text', [
            'name'  => 'filter_invoice_to',
            'label' => __('Ending With #'),
            'title' => __('Ending With #'),
            'note'  => __('Invoice number to end export with. Leave empty to ignore.'),
        ]);

        $iel4 = $fieldset->addField('filter_invoice_from_skip', 'select', [
            'name'   => 'filter_invoice_from_skip',
            'label'  => __('Skip Starting From'),
            'title'  => __('Skip Starting From'),
            'note'   => __('In case of "Yes" export will start from the order, next to the specified in the "Starting From #" field. Else specified order will be exported as well.'),
            'values' => [
                '0' => __('No'),
                '1' => __('Yes'),
            ],
        ]);

        $iel5 = $fieldset->addField('invoice_increment_auto', 'select', [
            'name'   => 'invoice_increment_auto',
            'label'  => __('Automatically Increment Starting From'),
            'title'  => __('Automatically Increment Starting From'),
            'note'   => __('Automatically fill "Starting From #" field with the last exported order number after each profile run'),
            'values' => [
                '0' => __('No'),
                '1' => __('Yes'),
            ],
        ]);


        /**
         * FIELDSET: Filters by shipment number
         */

        $fieldset = $form->addFieldset('filters_shipment_number_fieldset', ['legend' => __('Shipment Number Filters')]);

        $sel1 = $fieldset->addField(
            'filter_shipment_enabled', 'select', [
                'label'   => __('Use Shipment Number Filters'),
                'title'   => __('Use Shipment Number Filters'),
                'name'    => 'filter_shipment_enabled',
                'options' => [
                    '0' => __('No'),
                    '1' => __('Yes'),
                ],
            ]
        );

        $sel2 = $fieldset->addField('filter_shipment_from', 'text', [
            'name'  => 'filter_shipment_from',
            'label' => __('Starting From Shipment #'),
            'title' => __('Starting From Shipment #'),
            'note'  => __('Filter orders by shipment numbers.'),
        ]);

        $sel3 = $fieldset->addField('filter_shipment_to', 'text', [
            'name'  => 'filter_shipment_to',
            'label' => __('Ending With Shipment #'),
            'title' => __('Ending With Shipment #'),
            'note'  => __('Filter orders by shipment numbers.'),
        ]);


        /* start date block*/
        $fldDateRange = $fieldset->addFieldset('timeline', ['legend' => __('Date Range')]);
        $dateEnabled  = $fldDateRange->addField(
            'filter_date_enabled', 'select', [
                'label'   => __('Use Date Range'),
                'title'   => __('Use Date Range'),
                'name'    => 'filter_date_enabled',
                'options' => [
                    '0' => __('No'),
                    '1' => __('Yes'),
                ],
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );
        $fromDate   = $fldDateRange->addField(
            'filter_date_from',
            'date',
            [
                'name'         => 'filter_date_from',
                'label'        => __('From Date'),
                'title'        => __('From Date'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format'  => $dateFormat
            ]
        );

        $toDate = $fldDateRange->addField(
            'filter_date_to', 'date', [
                'name'         => 'filter_date_to',
                'label'        => __('To Date'),
                'title'        => __('To Date'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format'  => $dateFormat
            ]
        );
        /* end date block*/

        $fldGroup       = $form->addFieldset('customer_group', ['legend' => __('Customer Groups')]);
        $groupEnabled   = $fldGroup->addField(
            'filter_customergroup', 'select', [
                'label'   => __('Filter by Customer Group'),
                'title'   => __('Filter by Customer Group'),
                'name'    => 'filter_customergroup',
                'options' => [
                    '0' => __('No'),
                    '1' => __('Yes'),
                ],
            ]
        );
        $customerGroups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->create())->getItems();
        $groups         = $fldGroup->addField(
            'filter_customergroup_ids', 'multiselect', [
                'label'  => __('For Customer Groups'),
                'title'  => __('For Customer Groups'),
                'name'   => 'filter_customergroup_ids[]',
                'values' => $this->_objectConverter->toOptionArray($customerGroups, 'id', 'code'),
            ]
        );


        /**
         * FIELDSET: Other filters
         */

        $fieldset = $form->addFieldset('filters_other_fieldset', ['legend' => __('Other Export Filters')]);

//        $fieldset->addField('filter_skip_zero_price', 'select', [
//            'name'   => 'filter_skip_zero_price',
//            'label'  => __('Skip items with zero price'),
//            'title'  => __('Skip items with zero price'),
//            'note'   => __('Can be used to skip duplicated rows for configurable products purchases'),
//            'values' => [
//                '0' => __('No'),
//                '1' => __('Yes'),
//            ],
//        ]);
//
//        $fieldset->addField('filter_sku_onlylines', 'select', [
//            'name'   => 'filter_sku_onlylines',
//            'label'  => __('Include only lines with SKU found'),
//            'title'  => __('Include only lines with SKU found'),
//            'note'   => __('If set to "No", all products from orders with specified SKUs will be exported'),
//            'values' => [
//                '0' => __('No'),
//                '1' => __('Yes'),
//            ],
//        ]);
//
//        $fieldset->addField('filter_sku', 'textarea', [
//            'name'  => 'filter_sku',
//            'label' => __('Product SKU(s)'),
//            'title' => __('Product SKU(s)'),
//            'note'  => __('Export orders which contain listed products. Split multiple SKUs by comma (,) character'),
//        ]);

        $fieldset->addField('filter_status', 'multiselect', [
            'name'   => 'filter_status',
            'label'  => __('Order Status'),
            'title'  => __('Order Status'),
            'values' => $this->_helper->getOrderStatuses(),
        ]);


        $dependence = $this
            ->getLayout()
            ->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )
            ->addFieldMap($groupEnabled->getHtmlId(), $groupEnabled->getName())
            ->addFieldMap($groups->getHtmlId(), $groups->getName())
            ->addFieldDependence(
                $groups->getName(),
                $groupEnabled->getName(),
                '1'
            )
            ->addFieldMap($dateEnabled->getHtmlId(), $dateEnabled->getName())
            ->addFieldMap($fromDate->getHtmlId(), $fromDate->getName())
            ->addFieldMap($toDate->getHtmlId(), $toDate->getName())
            ->addFieldDependence(
                $fromDate->getName(),
                $dateEnabled->getName(),
                '1'
            )
            ->addFieldDependence(
                $toDate->getName(),
                $dateEnabled->getName(),
                '1'
            )
            ->addFieldMap($oel1->getHtmlId(), $oel1->getName())
            ->addFieldMap($oel2->getHtmlId(), $oel2->getName())
            ->addFieldMap($oel3->getHtmlId(), $oel3->getName())
            ->addFieldMap($oel4->getHtmlId(), $oel4->getName())
            ->addFieldMap($oel5->getHtmlId(), $oel5->getName())
            ->addFieldDependence(
                $oel2->getName(),
                $oel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $oel3->getName(),
                $oel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $oel4->getName(),
                $oel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $oel5->getName(),
                $oel1->getName(),
                '1'
            )
            ->addFieldMap($iel1->getHtmlId(), $iel1->getName())
            ->addFieldMap($iel2->getHtmlId(), $iel2->getName())
            ->addFieldMap($iel3->getHtmlId(), $iel3->getName())
            ->addFieldMap($iel4->getHtmlId(), $iel4->getName())
            ->addFieldMap($iel5->getHtmlId(), $iel5->getName())
            ->addFieldDependence(
                $iel2->getName(),
                $iel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $iel3->getName(),
                $iel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $iel4->getName(),
                $iel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $iel5->getName(),
                $iel1->getName(),
                '1'
            )
            ->addFieldMap($sel1->getHtmlId(), $sel1->getName())
            ->addFieldMap($sel2->getHtmlId(), $sel2->getName())
            ->addFieldMap($sel3->getHtmlId(), $sel3->getName())
            ->addFieldDependence(
                $sel2->getName(),
                $sel1->getName(),
                '1'
            )
            ->addFieldDependence(
                $sel3->getName(),
                $sel1->getName(),
                '1'
            );
        $this->setChild('form_after', $dependence);

        if (is_string($model->getData('filter_customergroup_ids')) && "" != $model->getData('filter_customergroup_ids')) {
            $model->setData('filter_customergroup_ids', @unserialize($model->getData('filter_customergroup_ids')));
        }

        if (is_string($model->getData('filter_status')) && "" != $model->getData('filter_status')) {
            $model->setData('filter_status', @unserialize($model->getData('filter_status')));
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
