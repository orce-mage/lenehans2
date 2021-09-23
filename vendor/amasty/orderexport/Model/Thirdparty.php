<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Model;

/**
 * Class Thirdparty
 *
 * @package Amasty\Orderexport\Model
 */
class Thirdparty extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Amasty\Orderexport\Helper\Data
     */
    protected $_helper;


    /**
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                  $date
     * @param \Magento\Framework\ObjectManagerInterface                    $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Customer\Model\Session                              $customerSession
     * @param \Magento\Catalog\Model\Product                               $product
     * @param \Amasty\Orderexport\Helper\Filter                            $helperFilter
     * @param \Amasty\Orderexport\Helper\Export                            $helperExport
     * @param \Amasty\Orderexport\Helper\Data                              $helper
     * @param \Amasty\Orderexport\Model\History                            $history
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|NULL $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|NULL           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Amasty\Orderexport\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = NULL,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = NULL,
        array $data = []
    ) {
        $this->_helper          = $helper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('Amasty\Orderexport\Model\ResourceModel\Thirdparty');
        $this->setIdFieldName('entity_id');
    }
}
