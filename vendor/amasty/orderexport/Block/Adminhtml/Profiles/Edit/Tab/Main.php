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


class Main extends Generic implements TabInterface
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
     * @var GroupManagementInterface
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
     * @param RuleFactory              $salesRule
     * @param GroupManagementInterface $groupManagement
     * @param ObjectConverter          $objectConverter
     * @param Store                    $systemStore
     * @param GroupRepositoryInterface $_groupRepository
     * @param SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        GroupManagementInterface $groupManagement,
        ObjectConverter $objectConverter,
        Store $systemStore,
        GroupRepositoryInterface $_groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Data $helper,
        array $data = []
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_systemStore = $systemStore;
        $this->_objectConverter = $objectConverter;
        $this->_groupRepository = $_groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_helper = $helper;
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
        return __('General');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('General');
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

        $fieldset = $form->addFieldset('general', ['legend' => __('Profile Information')]);
        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id', 'value' => $model->getId()]);
        }

        $name = $fieldset->addField(
            'name', 'text', [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'skip_child_products', 'select',
            [
                'label' => __('Skip Child Products'),
                'title' => __('Skip Child Products'),
                'name' => 'skip_child_products',
                'values' => [
                    '0' => __('No'),
                    '1' => __('Yes'),
                ],
            ]
        );

        $fieldset->addField(
            'skip_parent_products', 'select',
            [
                'label' => __('Skip Parent Products'),
                'title' => __('Skip Parent Products'),
                'name' => 'skip_parent_products',
                'values' => [
                    '0' => __('No'),
                    '1' => __('Yes'),
                ],
            ]
        );

        $fieldset = $form->addFieldset('orderstatus_fieldset', ['legend' => __('Status For Processed Orders')]);

        $statuses = $this->_helper->getOrderStatuses();
        $statuses = array_merge(['0' => __('- Do not change -')], $statuses);
        $fieldset->addField('post_status', 'select', [
            'name' => 'post_status',
            'label' => __('Change Status With'),
            'title' => __('Change Status With'),
            'values' => $statuses,
            'note' => __('Exported orders will get specified status after export'),
        ]);

        $fldGroup = $form->addFieldset('store_views', ['legend' => __('Store View')]);
        if (!$this->_storeManager->isSingleStoreMode()) {
            $stores = $fldGroup->addField(
                'store_ids',
                'multiselect',
                [
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true),
                    'name' => 'store_ids',
                    'required' => true
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $stores->setRenderer($renderer);
        } else {
            $stores = $fldGroup->addField(
                'store_ids',
                'hidden',
                ['name' => 'store_ids', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
        }

        $fieldset = $form->addFieldset('orderstatus_automatic_execution', ['legend' => __('Automatic Execution')]);

        $fieldset->addField(
            'run_after_order_creation', 'select',
            [
                'label' => __('Run After Each New Order is Placed'),
                'title' => __('Run After Each New Order is Placed'),
                'name' => 'run_after_order_creation',
                'values' => [
                    '0' => __('No'),
                    '1' => __('Yes'),
                ],
            ]
        );
        $cronField = $fieldset->addField(
            'run_by_cron', 'select',
            [
                'label' => __('Run Profile by Cron'),
                'title' => __('Run Profile by Cron'),
                'name' => 'run_by_cron',
                'values' => [
                    '0' => __('No'),
                    '1' => __('Yes'),
                ],
            ]
        );
        $cronSchedule = $fieldset->addField(
            'cron_schedule', 'text',
            [
                'label' => __('Profile Cron Schedule'),
                'title' => __('Profile Cron Schedule'),
                'name' => 'cron_schedule',
                'note' => __('Enter cron expression<br/>
                        <br/>
                        * * * * *<br/>
                        | | | | |<br/>
                        | | | | +---- Day of the Week   (range: 0-6, 1 standing for Monday)<br/>
                        | | | +------ Month of the Year (range: 1-12)<br/>
                        | | +-------- Day of the Month  (range: 1-31)<br/>
                        | +---------- Hour              (range: 0-23)<br/>
                        +------------ Minute            (range: 0-59)<br/>
                        Example: */5 * * * * - every five minutes<br/>
                        <br/>
                        Read more about cron expressions - <a href="https://en.wikipedia.org/wiki/Cron" target="_blank">here</a>'),
                'required' => true
            ]
        );

        if (!$model->getId()) {
            $form->addValues([
                'run_after_order_creation' => '0',
                'run_by_cron' => '0'
            ]);
        }
        $form->setValues($model->getData());

        // define field dependencies
        /**
         * @var \Magento\Backend\Block\Widget\Form\Element\Dependence
         */
        $dependence = $this
            ->getLayout()
            ->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )
            // Customer Groups
            ->addFieldMap($stores->getHtmlId(), $stores->getName())
            ->addFieldMap($name->getHtmlId(), $name->getName())
            ->addFieldMap($cronField->getHtmlId(), $cronField->getName())
            ->addFieldMap($cronSchedule->getHtmlId(), $cronSchedule->getName())
            ->addFieldDependence(
                $cronSchedule->getName(),
                $cronField->getName(),
                '1'
            );
        $this->setChild('form_after', $dependence);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
