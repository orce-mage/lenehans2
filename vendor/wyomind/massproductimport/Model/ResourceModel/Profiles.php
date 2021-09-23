<?php

namespace Wyomind\MassProductImport\Model\ResourceModel;

class Profiles extends \Wyomind\MassStockUpdate\Model\ResourceModel\Profiles
{

    /**
     * @var string
     */
    public $module = "MassProductImport";
    /**
     * @var string
     */
    protected $entity = 'Profiles';
    /**
     * @var array
     */
    protected $fieldsNotToCheck = [
        'imported_at',
        'last_import_report',
    ];
}
