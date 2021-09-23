<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Number\Format;

use Amasty\Number\Model\ConfigProvider;
use Amasty\Number\Model\Number\AbstractFormatter;
use Amasty\Number\Model\SequenceStorage;
use Magento\Store\Model\StoreManagerInterface;

class StoreFormatter extends AbstractFormatter
{
    const PLACEHOLDER = 'store';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ConfigProvider $configProvider,
        SequenceStorage $sequenceStorage,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($configProvider, $sequenceStorage);
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $template
     * @return string
     */
    public function format(string $template): string
    {
        try {
            if ($this->getSequence()->getOrder()) {
                $replacement = $this->getSequence()->getOrder()->getStoreId();
            } else {
                $replacement = $this->storeManager->getStore()->getId();
            }
        } catch (\Exception $e) {
            $type = $this->getSequence()->getEntityType();
            $replacement = $this->getSequence()->getCounterScope($type)->getScopeId();
        }

        return $this->replacePlaceholder($template, self::PLACEHOLDER, (string)$replacement);
    }
}
