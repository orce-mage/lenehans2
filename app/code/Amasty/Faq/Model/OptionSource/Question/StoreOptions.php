<?php

namespace Amasty\Faq\Model\OptionSource\Question;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store;

class StoreOptions implements OptionSourceInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $store;

    public function __construct(
        Store $store
    ) {
        $this->store = $store;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return $this->store->getStoreValuesForForm(false, true);
    }
}
