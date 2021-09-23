<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Source;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class StockStatus implements OptionSourceInterface
{
    const ATTIRUBTE_CODE = 'custom_stock_status';

    /**
     * @var array|null
     */
    private $options;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $options = [[
                'value' => 0,
                'label' => ''
            ]];

            try {
                $stockStatusAttribute = $this->attributeRepository->get(
                    Product::ENTITY,
                    self::ATTIRUBTE_CODE
                );
                foreach ($stockStatusAttribute->getOptions() as $attributeOption) {
                    $options[] = [
                        'value' => $attributeOption->getValue(),
                        'label' => $attributeOption->getLabel()
                    ];
                }
            } catch (NoSuchEntityException $e) {
                null;
            }

            $this->options = $options;
        }

        return $this->options;
    }
}
