<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Model\ResourceModel;

/**
 * Class Profiles
 * @package Wyomind\MassStockUpdate\Model\ResourceModel
 */
class Profiles extends \Wyomind\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var string
     */
    public $module = "MassStockUpdate";
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
    /**
     * @param $request
     * @return \Zend_Db_Statement_Interface
     */
    public function importProfile($request)
    {


        $connection = $this->getConnection('write');
        $request = str_replace("{{table}}", $this->getTable("" . strtolower($this->module) . "_profiles"), $request);
        return $connection->query($request);

    }

    /**
     * @return string
     * @throws \Zend_Db_Statement_Exception
     */
    public function getLastImportedProfileId()
    {


        $connection = $this->getConnection('write');

        $res = $connection->query("SELECT LAST_INSERT_ID() as id");

        return $res->fetch();
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(strtolower($this->module) . '_profiles', 'id');
    }
}
