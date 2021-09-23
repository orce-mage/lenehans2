<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Action\Export;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;

class DataCleanUpAction implements ActionInterface
{
    public function execute(ExportProcessInterface $exportProcess)
    {
        $data = $exportProcess->getData();
        foreach ($data as $key => $row) {
            if ($this->isRemoveRow($row)) {
                unset($data[$key]);
            }
        }

        $exportProcess->setData($data);
    }

    public function isRemoveRow(array $row): bool
    {
        $emptyRow = true;
        foreach ($row as $value) {
            if (!is_array($value)) {
                $emptyRow = false;
            } elseif (!empty($value) && !$this->isRemoveRow($value)) {
                $emptyRow = false;
            }
        }

        return $emptyRow;
    }

    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize(ExportProcessInterface $exportProcess)
    {
    }
}
