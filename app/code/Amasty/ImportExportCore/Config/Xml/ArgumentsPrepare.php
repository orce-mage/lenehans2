<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportExportCore
 */


declare(strict_types=1);

namespace Amasty\ImportExportCore\Config\Xml;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterfaceFactory;

class ArgumentsPrepare
{
    /**
     * @var ArgumentInterfaceFactory
     */
    private $argumentFactory;

    public function __construct(ArgumentInterfaceFactory $argumentFactory)
    {
        $this->argumentFactory = $argumentFactory;
    }

    /**
     * @param array $arguments
     *
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface[]
     */
    public function execute($arguments): array
    {
        if (empty($arguments)) {
            return [];
        }

        return $this->prepareArguments($arguments);
    }

    private function prepareArguments($arguments): array
    {
        $result = [];
        foreach ($arguments as $argumentData) {
            $argument = $this->argumentFactory->create();
            $argument->setName($argumentData['name']);
            $argument->setType($argumentData['xsi:type']);
            if ($argument->getType() === 'array' && !empty($argumentData['item'])) {
                $argument->setItems($this->prepareArguments($argumentData['item']));
            } else {
                $argument->setValue($argumentData['value']);
            }
            $result[] = $argument;
        }

        return $result;
    }
}
