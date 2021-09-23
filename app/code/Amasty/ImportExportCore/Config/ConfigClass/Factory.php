<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportExportCore
 */


declare(strict_types=1);

namespace Amasty\ImportExportCore\Config\ConfigClass;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var InterpreterInterface
     */
    private $argumentInterpreter;

    public function __construct(
        ObjectManagerInterface $objectManager,
        InterpreterInterface $argumentInterpreter
    ) {
        $this->objectManager = $objectManager;
        $this->argumentInterpreter = $argumentInterpreter;
    }

    public function createObject(ConfigClassInterface $configClass)
    {
        return $this->objectManager->create(
            $configClass->getName(),
            ['config' => $this->prepareArguments($this->convertArguments($configClass->getArguments()))]
        );
    }

    private function prepareArguments(array $arguments): array
    {
        $result = [];
        foreach ($arguments as $key => $argument) {
            $result[$key] = $this->argumentInterpreter->evaluate($argument);
        }

        return $result;
    }

    /**
     * @param \Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface[] $arguments
     *
     * @return array
     */
    private function convertArguments(array $arguments): array
    {
        $result = [];
        foreach ($arguments as $argument) {
            $row = ['name' => $argument->getName(), 'xsi:type' => $argument->getType()];
            if ($argument->getType() === 'array') {
                $row['item'] = $this->convertArguments($argument->getItems());
            } else {
                $row['value'] = $argument->getValue();
            }

            $result[$argument->getName()] = $row;
        }

        return $result;
    }
}
