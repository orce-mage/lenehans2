<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Export\DataHandling\AbstractModifier;
use Amasty\ExportCore\Export\DataHandling\ModifierProvider;
use Magento\Store\Model\StoreManagerInterface;

class WebsiteId2WebsiteCode extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @var array|null
     */
    private $map;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        $config
    ) {
        parent::__construct($config);
        $this->storeManager = $storeManager;
    }

    public function transform($value)
    {
        $map = $this->getMap();
        return $map[$value] ?? $value;
    }

    /**
     * Get website Id to website code map
     *
     * @return array
     */
    private function getMap()
    {
        if (!$this->map) {
            $this->map = [];
            $websites = $this->storeManager->getWebsites();
            foreach ($websites as $website) {
                $this->map[$website->getId()] = $website->getCode();
            }
        }
        return $this->map;
    }

    public function getGroup(): string
    {
        return ModifierProvider::CUSTOM_GROUP;
    }

    public function getLabel(): string
    {
        return __('Website Id to Website Code')->getText();
    }
}
