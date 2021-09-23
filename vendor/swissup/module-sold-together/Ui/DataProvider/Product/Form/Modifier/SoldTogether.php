<?php

namespace Swissup\SoldTogether\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Phrase;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Framework\Exception\NoSuchEntityException;
use Swissup\SoldTogether\Model\AbstractModel as SoldtogetherAbstractModel;

class SoldTogether extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Related   //AbstractModifier
{
    const DATA_SOLD_SCOPE = '';
    const DATA_SCOPE_ORDER = 'sold_order';
    const DATA_SCOPE_CUSTOMER = 'sold_customer';
    const GROUP_SOLDTOGETHER = 'soldtogether';

    protected $soldPrefix;

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                static::GROUP_SOLDTOGETHER => [
                    'children' => [
                        $this->soldPrefix . static::DATA_SCOPE_ORDER => $this->getOrderFieldset(),
                        $this->soldPrefix . static::DATA_SCOPE_CUSTOMER => $this->getCustomerFieldset(),
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('SoldTogether'),
                                'collapsible' => true,
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::DATA_SOLD_SCOPE,
                                'sortOrder' => 300
                            ],
                        ],

                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->locator->getProduct();
        $productId = $product->getId();
        if (!$productId) {
            return $data;
        }

        $pricemod = ObjectManager::getInstance()->get(
            \Magento\Catalog\Ui\Component\Listing\Columns\Price::class
        );
        $pricemod->setData('name', 'price');
        foreach ($this->getDataScopes() as $dataScope) {
            $data[$productId]['links'][$dataScope] = [];
            $resourceModel = $this->getSoldtogetherResourceModel($dataScope)
                ->getRelatedProductData($productId);

            foreach ($resourceModel as $rId => $rData) {
                /** @var \Magento\Catalog\Model\Product $linkedProduct */
                try {
                    $linkedProduct = $this->productRepository->getById(
                        $rId,
                        false,
                        $this->locator->getStore()->getId()
                    );
                } catch (NoSuchEntityException $e) {
                    // Linked product not found.
                    continue;
                }

                $data[$productId]['links'][$dataScope][] = $this->fillSoldTogetherData(
                    $linkedProduct,
                    SoldtogetherAbstractModel::MAX_WEIGHT - $rData['weight']
                );
            }

            if (!empty($data[$productId]['links'][$dataScope])) {
                $dataMap = $pricemod->prepareDataSource([
                    'data' => [
                        'items' => $data[$productId]['links'][$dataScope]
                    ]
                ]);
                $data[$productId]['links'][$dataScope] = $dataMap['data']['items'];
            }
        }

        $data[$productId][self::DATA_SOURCE_DEFAULT]['current_product_id'] = $productId;
        $data[$productId][self::DATA_SOURCE_DEFAULT]['current_store_id'] = $this->locator->getStore()->getId();

        return $data;
    }

    /**
     * Prepare data column
     *
     * @param  \Magento\Catalog\Api\Data\ProductInterface $product
     * @param  int                                        $position
     * @return array
     */
    private function fillSoldTogetherData(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        $position
    ) {
        return [
            'id' => $product->getId(),
            'thumbnail' => $this->imageHelper->init($product, 'product_listing_thumbnail')->getUrl(),
            'name' => $product->getName(),
            'status' => $this->status->getOptionText($product->getStatus()),
            'attribute_set' => $this->attributeSetRepository
                ->get($product->getAttributeSetId())
                ->getAttributeSetName(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'position' => $position,
        ];
    }

    /**
     * Prepare fieldset for order grid
     *
     * @return array
     */
    protected function getOrderFieldset()
    {
        $content = __(
            'Frequently Bought Together'
        );
        return [
            'children' => [
                'button_set' => $this->getSoldOrderButtonSet(
                    $content,
                    __('Add Frequently Bought Products'),
                    $this->soldPrefix . static::DATA_SCOPE_ORDER
                ),
                'modal' => $this->getGenericModal(
                    __('Add Frequently Bought Products'),
                    $this->soldPrefix . static::DATA_SCOPE_ORDER
                ),
                static::DATA_SCOPE_ORDER => $this->getGrid($this->soldPrefix . static::DATA_SCOPE_ORDER),
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label' => __('Frequently Bought Products'),
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'sortOrder' => 10,
                    ],
                ],
            ]
        ];
    }

    /**
     * Prepare fieldset for customer grid
     *
     * @return array
     */
    protected function getCustomerFieldset()
    {
        $content = __(
            'Customers Who Bought This Item Also Bought'
        );

        return [
            'children' => [
                'button_set' => $this->getSoldCustomerButtonSet(
                    $content,
                    __('Add Customers Bought Products'),
                    $this->soldPrefix . static::DATA_SCOPE_CUSTOMER
                ),
                'modal' => $this->getGenericModal(
                    __('Add Customers Bought Products'),
                    $this->soldPrefix . static::DATA_SCOPE_CUSTOMER
                ),
                static::DATA_SCOPE_CUSTOMER => $this->getGrid($this->soldPrefix . static::DATA_SCOPE_CUSTOMER),
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label' => __('Customers Who Bought This Item Also Bought Products'),
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'sortOrder' => 20,
                    ],
                ],
            ]
        ];
    }

    /**
     * Prepare buttons set for order grid
     *
     * @param  Phrase $content
     * @param  Phrase $buttonTitle
     * @param  string $scope
     * @return array
     */
    private function getSoldOrderButtonSet(Phrase $content, Phrase $btnTitle, $scope)
    {
        $resourceOrder = $this->getSoldtogetherResourceModel(
            static::DATA_SCOPE_ORDER
        );
        $contentHtml = (string)$content;
        if ($resourceOrder->isCondenseDataRequired()) {
            // add message that condese data is required
            $contentHtml = '<div class="message message-error error">'
                . __(
                        'There are duplicated "Frequently bought together" relations. Click this link to fix it - <a href="%1">Condense relations</a>.',
                        $this->getCondenseUrl('soldtogether/order/condense')
                    )
                . '</div>';
        } else {
            // there is no need to condense data
            $modal = 'product_form.product_form.soldtogether.sold_order.modal';
            $set['children'] = [
                'button_' . $scope => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => $modal,
                                        'actionName' => 'toggleModal',
                                    ],
                                    [
                                        'targetName' => $modal . '.' . $scope . '_product_listing',
                                        'actionName' => 'render',
                                    ]
                                ],
                                'title' => $btnTitle,
                                'provider' => null,
                            ],
                        ],
                    ],
                ],
            ];
        }

        $set['arguments'] = [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content' => $contentHtml,
                        'template' => 'ui/form/components/complex',
                    ],
                ],
            ];

        return $set;
    }

    /**
     * Prepare buttons set fro customer grid
     *
     * @param  Phrase $content
     * @param  Phrase $buttonTitle
     * @param  string $scope
     * @return array
     */
    private function getSoldCustomerButtonSet(
        Phrase $content,
        Phrase $buttonTitle,
        $scope
    ) {
        $resourceCustomer = $this->getSoldtogetherResourceModel(
            static::DATA_SCOPE_CUSTOMER
        );

        $contentHtml = (string)$content;
        if ($resourceCustomer->isCondenseDataRequired()) {
            // add message that condese data is required
            $contentHtml = '<div class="message message-error error">'
                . __(
                        'There are duplicated "Customers also bought" relations. Click this link to fix it - <a href="%1">Condense relations</a>.',
                        $this->getCondenseUrl('soldtogether/customer/condense')
                    )
                . '</div>';
        } else {
            // there is no need to condense data
            $modal = 'product_form.product_form.soldtogether.sold_customer.modal';
            $set['children'] = [
                'button_' . $scope => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => $modal,
                                        'actionName' => 'toggleModal',
                                    ],
                                    [
                                        'targetName' => $modal . '.' . $scope . '_product_listing',
                                        'actionName' => 'render',
                                    ]
                                ],
                                'title' => $buttonTitle,
                                'provider' => null,
                            ],
                        ],
                    ],

                ]
            ];
        }

        $set['arguments'] = [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content' => $contentHtml,
                        'template' => 'ui/form/components/complex',
                    ],
                ],
            ];

        return $set;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataScopes()
    {
        return [
            static::DATA_SCOPE_ORDER,
            static::DATA_SCOPE_CUSTOMER,
        ];
    }

    /**
     * Build URL to condence relations action
     *
     * @param  string $path
     * @return string
     */
    protected function getCondenseUrl($path)
    {
        $product = $this->locator->getProduct();
        $params = [
            'back' => 'catalogProductEdit',
            'id' => $product->getId()
        ];
        return $this->urlBuilder->getUrl($path, $params);
    }

    /**
     * Get resource model instance
     *
     * @param  string $dataScope
     * @return \Swissup\SoldTogether\Model\ResourceModel\AbstractResourceModel
     */
    private function getSoldtogetherResourceModel($dataScope)
    {
        $objectManager = ObjectManager::getInstance();
        if ($dataScope == static::DATA_SCOPE_CUSTOMER) {
            return $objectManager
                ->get('Swissup\SoldTogether\Model\ResourceModel\Customer');
        }

        return $objectManager
                ->get('Swissup\SoldTogether\Model\ResourceModel\Order');
    }
}
