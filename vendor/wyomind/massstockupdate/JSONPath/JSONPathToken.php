<?php

/**
 * JSONPath implementation for PHP.
 *
 */
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare (strict_types=1);
namespace Wyomind\MassStockUpdate\JSONPath;

use function class_exists;
use function in_array;
use function ucfirst;
class JSONPathToken
{
    /*
     * Tokens
     */
    public const T_INDEX = 'index';
    public const T_RECURSIVE = 'recursive';
    public const T_QUERY_RESULT = 'queryResult';
    public const T_QUERY_MATCH = 'queryMatch';
    public const T_SLICE = 'slice';
    public const T_INDEXES = 'indexes';
    /**
     * @var string
     */
    public $type;
    public $value;
    public function __construct(\Wyomind\MassStockUpdate\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->validateType($type);
        $this->type = $type;
        $this->value = $value;
    }
    /**
     * @throws JSONPathException
     */
    public function validateType(string $type) : void
    {
        if (!in_array($type, static::getTypes(), true)) {
            throw new JSONPathException('Invalid token: ' . $type);
        }
    }
    public static function getTypes() : array
    {
        return [static::T_INDEX, static::T_RECURSIVE, static::T_QUERY_RESULT, static::T_QUERY_MATCH, static::T_SLICE, static::T_INDEXES];
    }
    /**
     * @throws JSONPathException
     */
    public function buildFilter(bool $options)
    {
        $filterClass = 'Wyomind\\MassStockUpdate\\JSONPath\\Filters\\' . ucfirst($this->type) . 'Filter';
        if (!class_exists($filterClass)) {
            throw new JSONPathException("No filter class exists for token [{$this->type}]");
        }
        return new $filterClass($this, $options);
    }
}