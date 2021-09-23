<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\SchemaReader\Config;

class Reader extends \Amasty\ImportExportCore\Config\SchemaReader\Reader
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config/entity' => 'code',
        '/config/entity/behaviors/(behavior|custom)' => 'code',
        '/config/entity/fieldsConfig/fields/field' => 'name',
        '/config/relation' => 'code'
    ];
}
