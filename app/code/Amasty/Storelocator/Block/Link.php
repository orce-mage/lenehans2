<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block;

use Amasty\Storelocator\Model\ConfigProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\DefaultPathInterface;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        ConfigProvider $configProvider,
        DefaultPathInterface $defaultPath,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if (!$this->hasData('path')) {
            $this->setData('path', $this->configProvider->getUrl());
        }

        return $this->getData('path');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->configProvider->getLabel();
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return false;
    }
}
