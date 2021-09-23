<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\RelationSource\Xml;

use Amasty\ExportCore\Api\Config\Relation\RelationInterface;
use Amasty\ExportCore\Api\Config\Relation\RelationInterfaceFactory;
use Magento\Framework\Data\Argument\InterpreterInterface;

class RelationsConfigPrepare
{
    /**
     * @var RelationInterfaceFactory
     */
    private $relationFactory;

    /**
     * @var InterpreterInterface
     */
    private $argumentInterpreter;

    public function __construct(
        RelationInterfaceFactory $relationFactory,
        InterpreterInterface $argumentInterpreter
    ) {
        $this->relationFactory = $relationFactory;
        $this->argumentInterpreter = $argumentInterpreter;
    }

    /**
     * @param array $xmlRelationsConfig
     * @return RelationInterface[]
     */
    public function execute(array $xmlRelationsConfig): array
    {
        $relations = [];
        foreach ($xmlRelationsConfig as $relationConfigData) {
            if (!empty($relationConfigData['arguments'])) {
                $arguments = [];
                foreach ($relationConfigData['arguments'] as $key => $argumentData) {
                    $arguments[$key] = $this->argumentInterpreter->evaluate($argumentData);
                }
                $relationConfigData['arguments'] = $arguments;
            }
            $relationConfig = $this->relationFactory->create(['data' => $relationConfigData]);
            $relations[$relationConfig->getSubEntityFieldName()] = $relationConfig;
        }

        return $relations;
    }
}
