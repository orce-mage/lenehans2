<?php
namespace Swissup\SoldTogether\Block\Adminhtml;

class Reindex extends \Magento\Framework\View\Element\Template
{
    public function getIndexingUrl()
    {
        return $this->getUrl('*/*/reindex');
    }
}
