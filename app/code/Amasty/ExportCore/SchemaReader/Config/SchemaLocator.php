<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\SchemaReader\Config;

use Magento\Framework\Config\Dom\UrnResolver;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * @var UrnResolver
     */
    private $urnResolver;

    public function __construct(UrnResolver $urnResolver)
    {
        $this->urnResolver = $urnResolver;
    }

    public function getSchema()
    {
        return $this->urnResolver->getRealPath('urn:amasty:module:Amasty_ExportCore:etc/am_export.xsd');
    }

    public function getPerFileSchema()
    {
        return $this->getSchema();
    }
}
