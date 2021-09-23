<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Config\Entity\SampleData;

interface RowInterface
{
    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\SampleData\ValueInterface[]
     */
    public function getValues();

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\SampleData\ValueInterface[] $values
     *
     * @return void
     */
    public function setValues($values);
}
