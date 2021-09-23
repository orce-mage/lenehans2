<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model;

class Variable extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magetrend\Email\Helper\Data|null
     */
    public $helper = null;

    /**
     * Variable constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magetrend\Email\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magetrend\Email\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_construct();
    }

    protected function _construct()
    {
        $this->_init('Magetrend\Email\Model\ResourceModel\Variable');
    }

    /**
     * Load variable object by hash
     * @param $hash
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByHash($hash)
    {
        $collection = $this->getResourceCollection()
            ->addFieldToFilter('hash', $hash)
            ->setPageSize(1);

        foreach ($collection as $object) {
            return $object;
        }
        return $this;
    }

    /**
     * Save variable object
     * @return $this
     */
    public function save()
    {
        if (!$this->hasData('hash') || $this->getHash() == null) {
            $hash = $this->helper
                ->getHash($this->getVarKey(), $this->getBlockName(), $this->getBlockId(), $this->getTemplateId());
            $this->setHash($hash);
        }

        if ($this->_registry->registry('mtemail_var_tmp_flag')) {
            $this->setTmp(1);
        } else {
            $this->setTmp(0);
        }

        return parent::save();
    }
}
