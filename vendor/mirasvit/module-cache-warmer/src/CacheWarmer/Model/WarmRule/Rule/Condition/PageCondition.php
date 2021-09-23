<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Model\WarmRule\Rule\Condition;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\UrlInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Repository\SourceRepositoryInterface;

/**
 * @method getAttribute()
 * @method getJsFormObject()
 */
class PageCondition extends AbstractCondition
{
    /**
     * @var UrlInterface
     */
    private $urlManager;

    /**
     * @var Context
     */
    private $context;

    /**
     * PageCondition constructor.
     * @param UrlInterface $urlManager
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlManager,
        Context $context,
        array $data = []
    ) {
        $this->urlManager = $urlManager;
        $this->context    = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return $this|AbstractCondition
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'page_type'      => (string)__('Page Type'),
            'uri'            => (string)__('URI'),
            'popularity'     => (string)__('Popularity'),
            'product_type'   => (string)__('Product Type'),
            'customer_group' => (string)__('Customer Group'),
            'store'          => (string)__('Store'),
            'source'         => (string)__('URIs from the Source that'),
        ];

        asort($attributes);
        $this->setData('attribute_option', $attributes);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'page_type':
            case 'product_type':
            case 'customer_group':
            case 'store':
            case 'source':
                return 'multiselect';

            case 'popularity':
                return 'numeric';

            default:
                return 'string';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'page_type':
            case 'product_type':
            case 'customer_group':
            case 'store':
            case 'source':
                return 'multiselect';

            default:
                return 'text';
        }
    }

    /**
     * @param null|string $option
     * @return mixed
     */
    public function getValueOption($option = null)
    {
        $om      = ObjectManager::getInstance();
        $options = [];

        switch ($this->getAttribute()) {
            case 'page_type':
                /** @var \Mirasvit\CacheWarmer\Model\Config\Source\PageType $source */
                $source = $om->get(\Mirasvit\CacheWarmer\Model\Config\Source\PageType::class);
                foreach ($source->getPageTypes() as $type) {
                    $options[$type] = $type;
                }

                break;

            case 'product_type':
                $options = [
                    'simple'       => __('Simple'),
                    'bundle'       => __('Bundle'),
                    'downloadable' => __('Downloadable'),
                    'configurable' => __('Configurable'),
                    'grouped'      => __('Grouped'),
                    'virtual'      => __('Virtual'),
                ];

                break;

            case 'customer_group':
                /** @var \Magento\Customer\Model\ResourceModel\Group\Collection $groupCollection */
                $groupCollection = $om->get(\Magento\Customer\Model\ResourceModel\Group\Collection::class);
                foreach ($groupCollection as $group) {
                    $options[$group->getId()] = $group->getCustomerGroupCode();
                }

                break;

            case 'store':
                /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
                $storeManager = $om->get(\Magento\Store\Model\StoreManagerInterface::class);
                foreach ($storeManager->getStores() as $store) {
                    $options[$store->getId()] = $store->getName();
                }

                break;
            case 'source':
                /** @var SourceRepositoryInterface $sourceRepository */
                $sourceRepository = $om->get(SourceRepositoryInterface::class);

                foreach ($sourceRepository->getCollection() as $source) {
                    $options[$source->getId()] = $source->getSourceName();
                }

                break;
        }

        $this->setData('value_option', $options);

        return $this->getData('value_option' . ($option !== null ? '/' . $option : ''));
    }

    /**
     * {@inheritdoc}
     */
    public function getValueParsed()
    {
        return $this->getData('value');
    }

    /**
     * @param AbstractModel $object
     * @return bool
     */
    public function validate(AbstractModel $object)
    {
        if (!($object instanceof PageInterface)) {
            return true;
        }

        $result = null;

        switch ($this->getAttribute()) {
            case 'page_type':
                $value = $object->getPageType();

                $result = $this->validateAttribute($value);

                break;

            case 'customer_group':
                $varyData = $object->getVaryData();

                $value = isset($varyData['customer_group']) ? $varyData['customer_group'] : 0;

                $result = $this->validateAttribute($value);

                break;

            case 'popularity':
                $value = $object->getPopularity();

                $result = $this->validateAttribute($value);

                break;

            case 'uri':
                $value = $object->getUri();

                $result = $this->validateAttribute($value);

                break;

            case 'product_type':
                $productId = $object->getProductId();

                if (!$productId) {
                    $result = false;
                } else {
                    $om = ObjectManager::getInstance();

                    /** @var \Magento\Catalog\Model\Product $product */
                    $product = $om->create(\Magento\Catalog\Model\Product::class)
                        ->load($productId);

                    $result = $this->validateAttribute($product->getTypeId());
                }

                break;

            case 'store':
                if ($object->getStoreId()) {
                    $result = $this->validateAttribute($object->getStoreId());
                } else {
                    $result = true;
                }
                break;

            case 'source':
                $value = $object->getSourceId();

                $result = $this->validateAttribute($value);

                break;
            /** mp comment start */
            default:
                echo $this->getAttribute();
                die();
            /** mp comment end */
        }

        return $result;
    }
}
