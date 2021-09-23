<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Export\VirtualFields;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Api\VirtualField\GeneratorInterface;
use Amasty\ExportCore\Export\Action\Export\DataHandling\DataHandlerProvider;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ConfigClassFactory;

class VirtualFieldsAction implements ActionInterface
{
    /**
     * @var GeneratorInterface[]
     */
    private $fieldGenerators = [];

    /**
     * @var ConfigClassFactory
     */
    private $configClassFactory;

    /**
     * @var VirtualFieldsProvider
     */
    private $virtualFieldsProvider;

    public function __construct(
        ConfigClassFactory $configClassFactory,
        VirtualFieldsProvider $virtualFieldsProvider
    ) {
        $this->configClassFactory = $configClassFactory;
        $this->virtualFieldsProvider = $virtualFieldsProvider;
    }

    public function execute(ExportProcessInterface $exportProcess)
    {
        if (empty($this->fieldGenerators)) {
            return;
        }

        $data = $exportProcess->getData();
        $this->processData($data, $this->fieldGenerators, $exportProcess);

        $exportProcess->setData($data);
    }

    protected function processData(array &$data, $generators, ExportProcessInterface $exportProcess)
    {
        foreach ($data as &$row) {
            foreach ($generators as $field => $fieldGenerator) {
                if ($field === VirtualFieldsProvider::SUBENTITIES_KEY) {
                    foreach ($fieldGenerator as $subField => $subGenerators) {
                        if (!isset($row[$subField])) {
                            continue;
                        }
                        $this->processData(
                            $row[$subField],
                            $subGenerators,
                            $exportProcess
                        );
                    }

                    continue;
                }
                $row[$field] = $fieldGenerator->generateValue($row);
            }
        }
    }

    public function initialize(ExportProcessInterface $exportProcess)
    {
        $this->fieldGenerators = $this->virtualFieldsProvider->prepareVirtualFields($exportProcess);
    }
}
