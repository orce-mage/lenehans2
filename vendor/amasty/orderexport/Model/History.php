<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Model;

class History extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var  \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var  \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Amasty\Orderexport\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Amasty\Orderexport\Helper\Filter
     */
    protected $_helperFilter;

    /**
     * @var \Amasty\Orderexport\Helper\Export
     */
    protected $_helperExport;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Product $product,
        \Amasty\Orderexport\Helper\Filter $helperFilter,
        \Amasty\Orderexport\Helper\Export $helperExport,
        \Amasty\Orderexport\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = NULL,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = NULL,
        array $data = []
    ) {
        $this->date             = $date;
        $this->_product         = $product;
        $this->_storeManager    = $storeManager;
        $this->_objectManager   = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_helper          = $helper;
        $this->_helperExport    = $helperExport;
        $this->_helperFilter    = $helperFilter;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function addHistory($profile)
    {
        // empty the instance
        $this->unsetData();
        $this->clearInstance();

        $filePath = $profile->getData('file_path');

        // set data
        $this->setData('profile_id', $profile->getData('entity_id'));
        $this->setData('run_at', date("Y-m-d H:i:s"));
        $this->setData('last_increment_id', $profile->getData('last_increment_id'));
        $this->setData('last_invoice_increment_id', $profile->getData('last_invoice_increment_id'));
        $this->setData('file_path', $filePath);
        $this->setData('file_size', filesize(BP . DIRECTORY_SEPARATOR . $filePath));
        $this->setData('run_records', $profile->getData('run_records'));
        $this->setData('run_time', microtime(true) - $profile->getData('time_start'));

        // save
        $this->save();

        return $this->getId();
    }

    protected function _construct()
    {
        $this->_init('Amasty\Orderexport\Model\ResourceModel\History');
        $this->setIdFieldName('entity_id');
    }
}
