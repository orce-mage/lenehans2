<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\Entity\Field;

use Amasty\ExportCore\Api\Config\Entity\Field\ActionInterface;

class Action implements ActionInterface
{
    private $actionClass;

    public function getConfigClass()
    {
        return $this->actionClass;
    }

    public function setConfigClass($configClass): ActionInterface
    {
        $this->actionClass = $configClass;

        return $this;
    }
}
