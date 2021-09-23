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

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;

class WarmRule extends AbstractModel implements WarmRuleInterface
{
    /**
     * @var WarmRule\Rule
     */
    private $rule;

    /**
     * @var WarmRule\RuleFactory
     */
    private $ruleFactory;

    /**
     * WarmRule constructor.
     * @param WarmRule\RuleFactory $ruleFactory
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        WarmRule\RuleFactory $ruleFactory,
        Context $context,
        Registry $registry
    ) {
        $this->ruleFactory = $ruleFactory;

        parent::__construct($context, $registry);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\WarmRule::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($value)
    {
        return $this->setData(self::NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->getData(self::PRIORITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($value)
    {
        return $this->setData(self::PRIORITY, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }


    /**
     * {@inheritdoc}
     */
    public function setConditionsSerialized($value)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        try {
            $data = \Zend_Json::decode($this->getData(self::HEADERS_SERIALIZED));
            if (!$data) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaders(array $value)
    {
        return $this->setData(self::HEADERS_SERIALIZED, \Zend_Json::encode($value));
    }

    /**
     * {@inheritdoc}
     */
    public function getVaryData()
    {
        try {
            $data = \Zend_Json::decode($this->getData(self::VARY_DATA_SERIALIZED));
            if (!$data) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setVaryData(array $value)
    {
        return $this->setData(self::VARY_DATA_SERIALIZED, \Zend_Json::encode($value));
    }

    /**
     * @return WarmRule\Rule
     */
    public function getRule()
    {
        if (!$this->rule) {
            $this->rule = $this->ruleFactory->create()
                ->setData(self::CONDITIONS_SERIALIZED, $this->getData(self::CONDITIONS_SERIALIZED));
        }

        return $this->rule;
    }
}
