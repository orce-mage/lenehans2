<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Magento\Store\Model\StoreManagerInterface;

class WebsiteCode2WebsiteId implements FieldModifierInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array|null
     */
    private $map;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function transform($value)
    {
        $map = $this->getMap();
        return $map[$value] ?? $value;
    }

    private function getMap()
    {
        if (!$this->map) {
            $this->map = ['All Websites' => '0'];
            foreach ($this->storeManager->getWebsites() as $website) {
                $this->map[$website->getCode()] = (string)$website->getId();
            }
        }
        return $this->map;
    }
}
