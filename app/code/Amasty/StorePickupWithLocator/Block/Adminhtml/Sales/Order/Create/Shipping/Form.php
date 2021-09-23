<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Block\Adminhtml\Sales\Order\Create\Shipping;

use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\TimeHandler;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Amasty\StorePickupWithLocator\CustomerData\LocationData;

class Form extends Template
{
    /**
     * Path To Render Template
     */
    const TEMPLATE_PATH = 'Amasty_StorePickupWithLocator::sales/order/create/form.phtml';

    /**
     * @var LocationData
     */
    private $locationData;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var TimeHandler
     */
    private $timeHandler;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var array
     */
    private $storesData;

    public function __construct(
        Template\Context $context,
        LocationData $locationData,
        FormFactory $formFactory,
        TimeHandler $timeHandler,
        ConfigProvider $configProvider,
        SerializerInterface $jsonSerializer,
        array $data = []
    ) {
        $this->locationData = $locationData;
        $this->_template = self::TEMPLATE_PATH;
        $this->formFactory = $formFactory;
        $this->timeHandler = $timeHandler;
        $this->configProvider = $configProvider;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getOptionsForStores()
    {
        $locationCollection = $this->getStoresData();
        $locationsData = ['' => __('Please select a store.')];
        foreach ($locationCollection['stores'] as $location) {
            $locationsData[$location['id']] = $location['name'];
        }

        return $locationsData;
    }

    /**
     * @return string
     */
    public function getCurbsideLocationsMap(): string
    {
        $locationsData = [];
        $locationCollection = $this->getStoresData();
        foreach ($locationCollection['stores'] as $location) {
            $locationsData[$location['id']] = (bool)$location['curbside_enable'];
        }

        return $this->jsonSerializer->serialize($locationsData);
    }

    /**
     * @return array
     */
    public function getIntervals()
    {
        $timeIntervalOptions = $this->timeHandler->generate(TimeHandler::START_TIME, TimeHandler::END_TIME);
        $validDataForOptions = ['' => __('Please select time interval.')];
        foreach ($timeIntervalOptions as $interval) {
            $validDataForOptions[$interval['value']] = $interval['label'];
        }

        return $validDataForOptions;
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFormElements()
    {
        $form = $this->formFactory->create();
        $form->setHtmlIdPrefix('ampickup');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Store Pickup With Locator Data'),
                'class' => 'ampickup-order-fieldset'
            ]
        );

        $locationOptions = $this->getOptionsForStores();
        $fieldset->addField(
            'location_id',
            'select',
            [
                'label' => __('Select Store To Collect Your Order'),
                'name' => 'ampickup[location_id]',
                'required' => false,
                'options' => $locationOptions,
                'class' => 'ampickup-field'
            ]
        );

        $isCheckboxEnabled = $this->configProvider->isCurbsideCheckboxEnabled();
        $isCommentEnabled = $this->configProvider->isCurbsideCommentsEnabled();
        if ($isCheckboxEnabled || $isCommentEnabled) {
            $curbsideFieldset = $form->addFieldset(
                'curbside_fieldset',
                [
                    'class' => 'ampickup-order-fieldset ampickup-curbside-fieldset'
                ]
            );

            if ($isCheckboxEnabled) {
                $curbsideFieldset->addField(
                    'curbside_state',
                    'checkbox',
                    [
                        //'label' => $this->configProvider->getCurbsideCheckboxLabel(),
                        'name' => 'ampickup[is_curbside_pickup]',
                        'required' => false,
                        'class' => '',
                        'value' => 1,
                        'after_element_html' => ' ' . $this->configProvider->getCurbsideCheckboxLabel()
                    ]
                );
            }

            if ($isCommentEnabled) {
                $curbsideFieldset->addField(
                    'curbside_comment',
                    'textarea',
                    [
                        'name' => 'ampickup[curbside_pickup_comment]',
                        'required' => $this->configProvider->isCurbsideCommentRequired(),
                        'class' => 'ampickup-field'
                    ]
                );
            }
        }

        if ($this->configProvider->isPickupDateEnabled()) {
            $dateFieldset = $form->addFieldset(
                'date_fieldset',
                [
                    'class' => 'ampickup-order-fieldset'
                ]
            );
            $dateFormat = $this->timeHandler->getFormatDate();
            $dateFieldset->addField(
                'date',
                \Magento\Framework\Data\Form\Element\Date::class,
                [
                    'label' => __('Pickup Date'),
                    'name' => 'ampickup[date]',
                    'input_format' => $dateFormat,
                    'format' => $dateFormat,
                    'required' => false,
                    'date_format' => $dateFormat,
                    'class' => 'ampickup-field'
                ]
            );
            $intervalOptions = $this->getIntervals();
            $dateFieldset->addField(
                'interval_id',
                'select',
                [
                    'label' => __('Pickup Time'),
                    'name' => 'ampickup[tinterval_id]',
                    'required' => false,
                    'options' => $intervalOptions,
                    'class' => 'ampickup-field'
                ]
            );
        }

        return $form->getElements();
    }

    /**
     * @return array
     */
    private function getStoresData(): array
    {
        if ($this->storesData === null) {
            $this->storesData = $this->locationData->getSectionData();
        }

        return $this->storesData;
    }
}
