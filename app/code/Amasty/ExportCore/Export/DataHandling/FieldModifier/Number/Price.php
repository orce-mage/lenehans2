<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\DataHandling\FieldModifier\Number;

use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Export\DataHandling\AbstractModifier;
use Amasty\ExportCore\Export\DataHandling\ModifierProvider;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class Price extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Data $priceHelper,
        StoreManagerInterface $storeManager,
        $config
    ) {
        parent::__construct($config);
        $this->priceHelper = $priceHelper;
        $this->storeManager = $storeManager;
    }

    public function transform($value)
    {
        return strip_tags($this->priceHelper->currencyByStore($value, $this->storeManager->getStore()));
    }

    public function getGroup(): string
    {
        return ModifierProvider::NUMERIC_GROUP;
    }

    public function getLabel(): string
    {
        return __('Price in Base Currency')->getText();
    }
}
