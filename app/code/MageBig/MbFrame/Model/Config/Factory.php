<?php
/**
 * Copyright © 2020 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System configuration object factory
 */
namespace MageBig\MbFrame\Model\Config;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new config object
     *
     * @param array $data
     * @return \MageBig\MbFrame\Model\Config
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(\MageBig\MbFrame\Model\Config::class, $data);
    }
}
