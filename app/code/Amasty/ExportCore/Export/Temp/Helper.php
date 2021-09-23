<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Temp;

/**
 * temporary solution
 */
class Helper
{
    /**
     * @var array
     */
    private $headerStructure;

    public function setHeaderStructure(array $headerStructure): Helper
    {
        $this->headerStructure = $headerStructure;

        return $this;
    }

    public function getHeaderStructure(): ?array
    {
        return $this->headerStructure;
    }
}
