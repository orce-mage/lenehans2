<?php

namespace Amasty\Faq\Block;

use Amasty\Faq\Model\Url;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Amasty\Faq\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Amasty\Faq\Model\ResourceModel\Category\Collection
     */
    private $collection;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Faq\Model\ConfigProvider $configProvider,
        \Amasty\Faq\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        Url $url,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->configProvider = $configProvider;
        $this->url = $url;
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->configProvider->isEnabled()) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if (!$this->hasData('path')) {
            if ($this->configProvider->isUseFaqCmsHomePage()) {
                $this->setData('path', $this->url->getFaqUrl());
            } else {
                $this->setData(
                    'path',
                    $this->url->getCategoryUrl($this->collection->getFirstCategory())
                );
            }
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
}
