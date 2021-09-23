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



namespace Mirasvit\CacheWarmer\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Helper\Serializer;
use Mirasvit\Core\Service\CompatibilityService;

class Page extends AbstractModel implements PageInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Page constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Serializer $serializer
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Serializer $serializer,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Page::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(PageInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->getData(PageInterface::URI);
    }

    /**
     * {@inheritdoc}
     */
    public function setUri($value)
    {
        return $this->setData(PageInterface::URI, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriHash()
    {
        return $this->getData(PageInterface::URI_HASH);
    }

    /**
     * {@inheritdoc}
     */
    public function setUriHash($value)
    {
        return $this->setData(PageInterface::URI_HASH, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheId()
    {
        return $this->getData(PageInterface::CACHE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheId($value)
    {
        return $this->setData(PageInterface::CACHE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageType()
    {
        return $this->getData(PageInterface::PAGE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPageType($value)
    {
        return $this->setData(PageInterface::PAGE_TYPE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(PageInterface::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($value)
    {
        return $this->setData(PageInterface::PRODUCT_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryId()
    {
        return $this->getData(PageInterface::CATEGORY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryId($value)
    {
        return $this->setData(PageInterface::CATEGORY_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(PageInterface::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($value)
    {
        return $this->setData(PageInterface::STORE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function setVaryData($value)
    {
        if (is_array($value)) {
            ksort($value);
            $value = $this->serializer->serialize($value);
        }

        return $this->setData(PageInterface::VARY_DATA, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getVaryString()
    {
        if ($this->getVaryDataHash()) {
            return $this->getVaryDataHash();
        }
        //below is for backward compatibility
        $data = $this->getVaryData();
        if (!empty($data)) {
            ksort($data);
            return sha1($this->serializer->serialize($data));
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getVaryData()
    {
        $value = $this->serializer->unserialize($this->getData(PageInterface::VARY_DATA));

        if (is_array($value)) {
            ksort($value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getVaryDataHash()
    {
        return $this->getData(PageInterface::VARY_DATA_HASH);
    }

    /**
     * {@inheritdoc}
     */
    public function setVaryDataHash($value)
    {
        return $this->setData(PageInterface::VARY_DATA_HASH, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCookie()
    {
        return $this->getData(PageInterface::COOKIE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCookie($value)
    {
        return $this->setData(PageInterface::COOKIE, $value);
    }


    /**
     * {@inheritdoc}
     */
    public function getUserAgent()
    {
        $agent = $this->getData(PageInterface::USER_AGENT);
        if (!$agent) {
            return 'CacheWarmer';
        }
        return $agent;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserAgent($value)
    {
        return $this->setData(PageInterface::USER_AGENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttempts()
    {
        return $this->getData(PageInterface::ATTEMPTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttempts($value)
    {
        return $this->setData(PageInterface::ATTEMPTS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPopularity()
    {
        return $this->getData(PageInterface::POPULARITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setPopularity($value)
    {
        return $this->setData(PageInterface::POPULARITY, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(PageInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($value)
    {
        return $this->setData(PageInterface::STATUS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(PageInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(PageInterface::CREATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(PageInterface::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($value)
    {
        return $this->setData(PageInterface::UPDATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedAt()
    {
        return $this->getData(PageInterface::CACHED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCachedAt($value)
    {
        return $this->setData(PageInterface::CACHED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFlushedAt()
    {
        return $this->getData(PageInterface::FLUSHED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setFlushedAt($value)
    {
        return $this->setData(PageInterface::FLUSHED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getWarmRuleVersion()
    {
        return $this->getData(PageInterface::WARM_RULE_VERSION);
    }

    /**
     * {@inheritdoc}
     */
    public function setWarmRuleVersion($value)
    {
        return $this->setData(PageInterface::WARM_RULE_VERSION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getWarmRuleIds()
    {
        return array_filter(explode(',', $this->getData(PageInterface::WARM_RULE_IDS)));
    }

    /**
     * {@inheritdoc}
     */
    public function setWarmRuleIds(array $value)
    {
        return $this->setData(PageInterface::WARM_RULE_IDS, implode(',', $value));
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setMainRuleId($value)
    {
        return $this->setData('main_rule_id', $value);
    }

    /**
     * @return int|null
     */
    public function getMainRuleId()
    {
        return $this->getData('main_rule_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        try {
            $value = \Zend_Json::decode($this->getData(PageInterface::HEADERS));
            if (!$value) {
                return [];
            }
        } catch (\Exception $e) {
            $value = [];
        }

        if (is_array($value)) {
            ksort($value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaders(array $value)
    {
        if (is_array($value)) {
            ksort($value);
            $value = \Zend_Json::encode($value);
        }

        return $this->setData(PageInterface::HEADERS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceId()
    {
        return $this->getData('source_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setSourceId($value)
    {
        return $this->setData('source_id', $value);
    }
}
