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



namespace Mirasvit\CacheWarmer\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mirasvit\CacheWarmer\Api\Data\JobInterface;
use Mirasvit\CacheWarmer\Helper\Serializer;
use Mirasvit\Core\Service\CompatibilityService;

class Job extends AbstractDb
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(JobInterface::TABLE_NAME, JobInterface::ID);
    }

    /**
     * Job constructor.
     * @param Context $context
     * @param Serializer $serializer
     * @param null $connectionName
     */
    public function __construct(Context $context, Serializer $serializer, $connectionName = null)
    {
        parent::__construct($context, $connectionName);
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var \Mirasvit\CacheWarmer\Model\Job $object */
        $object->setData('filter_serialized', $this->serializer->serialize($object->getFilter()));
        $object->setData('info_serialized', $this->serializer->serialize($object->getInfo()));

        return parent::_beforeSave($object);
    }
}
