<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportExportCore
 */


declare(strict_types=1);

namespace Amasty\ImportExportCore\Config\ConfigClass;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;

class ConfigClass implements ConfigClassInterface
{
    /** @var string */
    private $name;

    /** @var array */
    private $arguments = [];

    public function __construct(string $name, ?string $baseType = '', ?array $arguments = [])
    {
        if (!empty($baseType) && !is_subclass_of($name, $baseType)) {
            throw new \LogicException(
                'Class ' . $name . ' doesn\'t implement ' . $baseType
            );
        }
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): ?array
    {
        return $this->arguments;
    }
}
