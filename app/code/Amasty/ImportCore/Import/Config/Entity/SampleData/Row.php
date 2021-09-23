<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\Entity\SampleData;

use Amasty\ImportCore\Api\Config\Entity\SampleData\RowInterface;

class Row implements RowInterface
{
    private $values;

    /**
     * @inheritDoc
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @inheritDoc
     */
    public function setValues($values)
    {
        $this->values = $values;
    }
}
