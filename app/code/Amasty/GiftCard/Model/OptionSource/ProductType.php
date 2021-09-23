<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class ProductType implements OptionSourceInterface
{

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $type;

    public function __construct(
        \Magento\Catalog\Model\Product\Type $type
    ) {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->type->getAllOptions();
    }
}
