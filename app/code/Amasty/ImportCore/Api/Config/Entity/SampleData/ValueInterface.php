<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Config\Entity\SampleData;

interface ValueInterface
{
    /**
     * @return string
     */
    public function getField();

    /**
     * @param string $field
     *
     * @return void
     */
    public function setField($field);

    /**
     * @return string
     */
    public function getValue();

    /**
     * @param string $value
     *
     * @return void
     */
    public function setValue($value);
}
