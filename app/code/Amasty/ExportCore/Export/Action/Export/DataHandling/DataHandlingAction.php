<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Export\DataHandling;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;

class DataHandlingAction implements ActionInterface
{
    /**
     * @var DataHandlerProvider
     */
    private $dataHandlerProvider;

    private $modifiers = null;

    public function __construct(
        DataHandlerProvider $dataHandlerProvider
    ) {
        $this->dataHandlerProvider = $dataHandlerProvider;
    }

    public function execute(
        ExportProcessInterface $exportProcess
    ) {
        if (empty($this->modifiers)) {
            return;
        }

        $data = $exportProcess->getData();
        $this->processData($data, $this->modifiers, $exportProcess);
        $exportProcess->setData($data);
    }

    protected function processData(array &$data, $modifiers, ExportProcessInterface $exportProcess)
    {
        foreach ($data as &$row) {
            foreach ($modifiers as $field => $fieldModifiers) {
                if ($field === DataHandlerProvider::SUBENTITIES_KEY) {
                    foreach ($fieldModifiers as $subField => $subModifier) {
                        if (!isset($row[$subField])) {
                            continue;
                        }
                        $this->processData(
                            $row[$subField],
                            $subModifier,
                            $exportProcess
                        );
                    }

                    continue;
                }
                /** @var FieldModifierInterface $modifier */
                foreach ($fieldModifiers as $modifier) {
                    try {
                        $modifier->prepareRowOptions($row);
                        $value = $modifier->transform($row[$field] ?? null);
                        if ($value !== null) {
                            $row[$field] = $value;
                        }
                    } catch (\Throwable $throwable) {
                        $exportProcess->addErrorMessage($throwable->getMessage());
                    }
                }
            }
        }
    }

    public function initialize(ExportProcessInterface $exportProcess)
    {
        $this->modifiers = $this->dataHandlerProvider->prepareModifiers($exportProcess);
    }
}
