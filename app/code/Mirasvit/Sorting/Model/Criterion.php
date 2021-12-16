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
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Sorting\Api\Data\CriterionInterface;

class Criterion extends AbstractModel implements CriterionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Criterion::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($value)
    {
        return $this->setData(self::CODE, $value);
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
    public function isDefault()
    {
        return $this->getData(self::IS_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsDefault($value)
    {
        return $this->setData(self::IS_DEFAULT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isSearchDefault()
    {
        return $this->getData(self::IS_SEARCH_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSearchDefault($value)
    {
        return $this->setData(self::IS_SEARCH_DEFAULT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($value)
    {
        return $this->setData(self::POSITION, $value);
    }

    public function getConditionCluster()
    {
        try {
            $data = \Zend_Json::decode($this->getData(self::CONDITIONS_SERIALIZED));

            $cluster = new Criterion\ConditionCluster();
            $cluster->loadArray($data);

            return $cluster;
        } catch (\Exception $e) {
            return new Criterion\ConditionCluster();
        }
    }

    public function setConditionCluster(Criterion\ConditionCluster $value)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, \Zend_Json::encode($value->toArray()));
    }
}
