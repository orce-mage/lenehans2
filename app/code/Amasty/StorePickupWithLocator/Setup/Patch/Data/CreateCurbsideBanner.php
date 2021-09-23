<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */

declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Setup\Patch\Data;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Cms\Model\Block;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;

class CreateCurbsideBanner implements DataPatchInterface
{
    /**
     * @var BlockInterfaceFactory
     */
    private $blockFactory;

    public function __construct(
        BlockInterfaceFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }

    /**
     * @return $this
     */
    public function apply()
    {
        /** @var BlockInterface|Block $block */
        $block = $this->blockFactory->create();
        $blockContent = '<div class="ampickup-curbside-banner">'
            . '<img src="{{view url=Amasty_StorePickupWithLocator::images/banner.svg}}" '
            . 'alt="Curbside Pickup" class="ampickup-curbside-banner-img">'
            . '</div>';

        $block
            ->setIdentifier('curbside_pickup_banner')
            ->setIsActive(Block::STATUS_ENABLED)
            ->setTitle('Pickup Option Banner')
            ->setContent($blockContent)
            ->setStores([Store::DEFAULT_STORE_ID])
            ->save();

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }
}
