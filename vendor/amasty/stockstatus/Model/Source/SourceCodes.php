<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Source;

use Amasty\Stockstatus\Model\ResourceModel\Inventory;
use Magento\Framework\Data\OptionSourceInterface;

class SourceCodes implements OptionSourceInterface
{
    const ALL_SOURCES = 'all_source_code';

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var array|null
     */
    private $options;

    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
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
                'value' => static::ALL_SOURCES,
                'label' => __('All Sources')
            ]];

            foreach ($this->inventory->getAllSources() as $sourceCode => $sourceLabel) {
                $options[] = [
                    'value' => $sourceCode,
                    'label' => $sourceLabel
                ];
            }

            $this->options = $options;
        }

        return $this->options;
    }
}
