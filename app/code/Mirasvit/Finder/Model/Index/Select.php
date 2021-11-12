<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Model\Index;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;

class Select
{
    const STATIC_FIELDS = ['entity_id', 'sku', 'attribute_set_id', 'type_id', 'created_at', 'updated_at'];

    private $resource;

    private $connection;

    private $eavConfig;

    public function __construct(
        ResourceConnection $resource,
        EavConfig $eavConfig
    ) {
        $this->resource   = $resource;
        $this->connection = $resource->getConnection();
        $this->eavConfig  = $eavConfig;
    }

    public function joinField(\Magento\Framework\DB\Select $select, string $fieldName): string
    {
        if (in_array($fieldName, self::STATIC_FIELDS)) {
            $fieldCondition = "e.{$fieldName}";

            $select->columns([
                $fieldName => $fieldCondition,
            ]);

            return $fieldCondition;
        }

        $attribute = $this->eavConfig->getAttribute('catalog_product', $fieldName);

        if (!$attribute->getId()) {
            return '';
        }

        $joinField = 'e.entity_id';

        $code = $attribute->getAttributeCode();

        if ($code == 'category_ids') {
            $table      = $this->resource->getTableName('catalog_category_product');
            $tableAlias = "tbl_{$code}";
            $field      = "{$tableAlias}.category_id";
            $select->joinLeft(
                [$tableAlias => $table],
                "{$joinField} = {$tableAlias}.product_id",
                [$code = $field]
            );

            $attribute = $this->eavConfig->getAttribute('catalog_category', 'name');

            if (!$attribute->getId()) {
                return '';
            }

            $code = $attribute->getAttributeCode();

            $joinField = 'tbl_category_ids.category_id';
        }

        $table = $attribute->getBackendTable();

        $tableAlias = "tbl_{$code}";

        $field = "{$tableAlias}.value";

        $condition = "{$joinField} = {$tableAlias}.entity_id AND {$tableAlias}.attribute_id = {$attribute->getId()}";

        $select->joinLeft(
            [$tableAlias => $table],
            $condition,
            [$code => $field]
        )->where($field . ' is not null');

        return $field;
    }
}
