<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\EntitySource\Xml;

use Amasty\ImportCore\Api\Config\Entity\FieldsConfigInterface;

interface FieldsClassInterface
{
    public function execute(FieldsConfigInterface $existingConfig): FieldsConfigInterface;
}
