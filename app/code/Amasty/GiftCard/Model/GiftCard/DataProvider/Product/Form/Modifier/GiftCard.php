<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\DataProvider\Product\Form\Modifier;

use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard as GiftCardProduct;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Directory\Helper\Data;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;

class GiftCard extends AbstractModifier
{
    const PRICES_PANEL_NAME = 'amasty-gift-card-prices';
    const FIELD_CONFIG_PREFIX = 'use_config_';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var Data
     */
    private $directoryHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    private $meta = [];

    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        Data $directoryHelper,
        ArrayManager $arrayManager,
        ConfigProvider $configProvider
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->directoryHelper = $directoryHelper;
        $this->arrayManager = $arrayManager;
        $this->configProvider = $configProvider;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        if ($this->locator->getProduct()->getTypeId()
            === GiftCardProduct::TYPE_AMGIFTCARD
        ) {
            $this->customizeAmount();
            $this->customizeUseConfigField(
                Attributes::EMAIL_TEMPLATE,
                Select::NAME,
                'admin__field-default'
            );
            $this->customizeUseConfigField(
                Attributes::GIFTCARD_LIFETIME,
                Input::NAME,
                'admin__field-small',
                ['validate-digits' => true]
            );
            $this->customizeAmountType();
            $this->customizeFeeType();
            $this->removeHasWeightField();
            $this->modifyGiftCardPricesPanel();
        }

        return $this->meta;
    }

    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();

        if ($product->getTypeId() === GiftCardProduct::TYPE_AMGIFTCARD) {
            $modelId = $product->getId();
            $value = '';

            if (isset($data[$modelId][static::DATA_SOURCE_DEFAULT][Attributes::EMAIL_TEMPLATE])) {
                $value = $data[$modelId][static::DATA_SOURCE_DEFAULT][Attributes::EMAIL_TEMPLATE];
            }

            if (!$value || $value == Attributes::ATTRIBUTE_CONFIG_VALUE) {
                $data[$modelId][static::DATA_SOURCE_DEFAULT][Attributes::EMAIL_TEMPLATE] =
                    $this->configProvider->getEmailTemplate();
                $data[$modelId][static::DATA_SOURCE_DEFAULT][self::FIELD_CONFIG_PREFIX . Attributes::EMAIL_TEMPLATE] =
                    '1';
            }
            $value = $data[$modelId][static::DATA_SOURCE_DEFAULT][Attributes::GIFTCARD_LIFETIME] ?? 0;

            if (!$value || $value == Attributes::ATTRIBUTE_CONFIG_VALUE) {
                $data[$modelId][static::DATA_SOURCE_DEFAULT][Attributes::GIFTCARD_LIFETIME] =
                    $this->configProvider->getLifetime();
                $data[$modelId][static::DATA_SOURCE_DEFAULT][self::FIELD_CONFIG_PREFIX
                . Attributes::GIFTCARD_LIFETIME] = '1';
            }
        }

        return $data;
    }

    /**
     * Move prices panel after product details
     */
    protected function modifyGiftCardPricesPanel()
    {
        if (isset($this->meta[self::PRICES_PANEL_NAME])) {
            $this->meta[self::PRICES_PANEL_NAME]['arguments']['data']['config']['sortOrder'] =
                static::GENERAL_PANEL_ORDER + 1;
        }
    }

    /**
     * Customize Amounts field
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function customizeAmount()
    {
        $amountPath = $this->arrayManager->findPath(
            Attributes::GIFTCARD_PRICES,
            $this->meta,
            null,
            'children'
        );

        if (!$amountPath) {
            return;
        }
        $meta = $this->arrayManager->merge(
            $amountPath,
            $this->meta,
            $this->getAmountStructure($amountPath)
        );
        $this->meta = $meta;
    }

    /**
     * Get Amounts dynamic rows structure
     *
     * @param string $amountPath
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getAmountStructure(string $amountPath): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Amounts'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' => $this->arrayManager->get(
                            $amountPath . '/arguments/data/config/sortOrder',
                            $this->meta
                        ),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'website_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Text::NAME,
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataScope' => 'website_id',
                                        'label' => __('Website'),
                                        'options' => $this->getWebsites(),
                                        'value' => $this->getDefaultWebsite(),
                                        'visible' => $this->isMultiWebsites(),
                                        'disabled' => ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()),
                                    ],
                                ],
                            ],
                        ],
                        'value' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Price::NAME,
                                        'label' => __('Amount'),
                                        'enableLabel' => true,
                                        'dataScope' => 'value',
                                        'required' => '1',
                                        'validation' => [
                                            'required-entry' => true,
                                            'validate-greater-than-zero' => true,
                                            'validate-number' => true
                                        ],
                                        'addbefore' => $this->locator->getStore()
                                            ->getBaseCurrency()
                                            ->getCurrencySymbol(),
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    protected function customizeAmountType()
    {
        $meta = $this->meta;
        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                Attributes::ALLOW_OPEN_AMOUNT,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'dataScope' => Attributes::ALLOW_OPEN_AMOUNT,
                'valueMap' => [
                    'false' => '0',
                    'true' => '1'
                ],
            ]
        );
        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                Attributes::OPEN_AMOUNT_MIN,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'imports' => [
                    'visible' => 'ns = ${$.ns}, index = ' . Attributes::ALLOW_OPEN_AMOUNT . ':checked',
                ],
                'validation' => [
                    'validate-number' => true
                ]
            ]
        );

        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                Attributes::OPEN_AMOUNT_MAX,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'imports' => [
                    'visible' => 'ns = ${$.ns}, index = ' . Attributes::ALLOW_OPEN_AMOUNT . ':checked',
                ],
                'validation' => [
                    'validate-number' => true
                ]
            ]
        );
        $this->meta = $meta;
    }

    /**
     * @return void
     */
    protected function customizeFeeType()
    {
        $meta = $this->meta;
        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                Attributes::FEE_ENABLE,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'dataScope' => Attributes::FEE_ENABLE,
            ]
        );
        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                Attributes::FEE_TYPE,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'imports' => [
                    'visible' => 'ns = ${$.ns}, index = ' . Attributes::FEE_ENABLE . ':checked',
                ]
            ]
        );

        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                Attributes::FEE_VALUE,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'imports' => [
                    'visible' => 'ns = ${$.ns}, index = ' . Attributes::FEE_ENABLE . ':checked',
                ]
            ]
        );
        $this->meta = $meta;
    }

    /**
     * Remove "Product Has Weight" field
     *
     * @return void
     */
    protected function removeHasWeightField()
    {
        $this->meta = $this->arrayManager->remove(
            $this->arrayManager->findPath('product_has_weight', $this->meta, null, 'children'),
            $this->meta
        );
    }

    /**
     * @param string $field
     * @param string $formElement
     * @param string $additionalClasses
     * @param array $validation
     *
     * @return void
     */
    protected function customizeUseConfigField(
        string $field,
        string $formElement,
        string $additionalClasses = '',
        array $validation = []
    ) {
        $meta = $this->meta;

        $groupCode = $this->getGroupCodeByField($meta, 'container_' . $field);

        if (!$groupCode) {
            return;
        }

        $containerPath = $this->arrayManager->findPath(
            'container_' . $field,
            $meta,
            null,
            'children'
        );
        $fieldPath = $this->arrayManager->findPath($field, $meta, null, 'children');
        $groupConfig = $this->arrayManager->get($containerPath, $meta);
        $fieldConfig = $this->arrayManager->get($fieldPath, $meta);

        $meta = $this->arrayManager->merge($containerPath, $meta, [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/group',
                        'label' => $groupConfig['arguments']['data']['config']['label'],
                        'breakLine' => false,
                        'sortOrder' => $fieldConfig['arguments']['data']['config']['sortOrder'],
                        'dataScope' => '',
                    ],
                ],
            ],
        ]);
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children' => [
                    $field => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'additionalClasses' => $additionalClasses,
                                    'dataType' => Text::NAME,
                                    'dataScope' => $field,
                                    'imports' => [
                                        'disabled' => '${$.parentName}.' . self::FIELD_CONFIG_PREFIX
                                            . $field
                                            . ':checked',
                                    ],
                                    'formElement' => $formElement,
                                    'validation' => $validation,
                                    'componentType' => Field::NAME
                                ],
                            ],
                        ],
                    ],
                    self::FIELD_CONFIG_PREFIX . $field => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'number',
                                    'formElement' => Checkbox::NAME,
                                    'componentType' => Field::NAME,
                                    'description' => __('Use Config Settings'),
                                    'dataScope' => self::FIELD_CONFIG_PREFIX . $field,
                                    'valueMap' => [
                                        'false' => '0',
                                        'true' => '1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->meta = $meta;
    }

    /**
     * Check am_giftcard_prices attribute scope is global
     *
     * @return bool
     */
    protected function isScopeGlobal(): bool
    {
        return $this->locator->getProduct()
            ->getResource()
            ->getAttribute(Attributes::GIFTCARD_PRICES)
            ->isScopeGlobal();
    }

    /**
     * Get websites list
     *
     * @return array
     */
    protected function getWebsites(): array
    {
        $websites = [
            [
                'label' => __('All Websites [%1]', $this->directoryHelper->getBaseCurrencyCode()),
                'value' => 0,
            ]
        ];
        $productWebsiteIds = $this->locator->getProduct()->getWebsiteIds();

        foreach ($this->storeManager->getWebsites() as $website) {
            if (!in_array($website->getId(), $productWebsiteIds)) {
                continue;
            }
            $websites[] = [
                'label' => __('%1 [%2]', $website->getName(), $website->getBaseCurrencyCode()),
                'value' => $website->getId(),
            ];
        }

        return $websites;
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDefaultWebsite(): int
    {
        if ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()) {
            return (int)$this->storeManager->getStore($this->locator->getProduct()->getStoreId())->getWebsiteId();
        }

        return 0;
    }

    /**
     * Show group prices grid website column
     *
     * @return bool
     */
    protected function isShowWebsiteColumn(): bool
    {
        if ($this->isScopeGlobal() || $this->storeManager->isSingleStoreMode()) {
            return false;
        }

        return true;
    }

    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     */
    protected function isMultiWebsites(): bool
    {
        return !$this->storeManager->isSingleStoreMode();
    }

    /**
     * Check is allow change website value for combination
     *
     * @return bool
     */
    protected function isAllowChangeWebsite(): bool
    {
        if (!$this->isShowWebsiteColumn() || $this->locator->getProduct()->getStoreId()) {
            return false;
        }

        return true;
    }
}
