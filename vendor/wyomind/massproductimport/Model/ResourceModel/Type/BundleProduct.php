<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class BundleProduct extends \Wyomind\MassProductImport\Model\ResourceModel\Type\AbstractResource
{
    const OPTIONS_CONTAINER = 'container1';

    public function _construct()
    {
        $this->tableCpe = $this->getTable('catalog_product_entity');
        $this->tableCpr = $this->getTable('catalog_product_relation');
        $this->tableCpbo = $this->getTable('catalog_product_bundle_option');
        $this->tableCpbov = $this->getTable('catalog_product_bundle_option_value');
        $this->tableCpbs = $this->getTable('catalog_product_bundle_selection');

        parent::_construct();
    }

    /**
     * Collect data for each product to update/import
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile $profile
     * @return void
     * @throws \Exception
     */
    public function collect($productId, $value, $strategy, $profile)
    {
        list($type) = $strategy['option'];
        $storeviews = $strategy['storeviews'];
        $title = str_replace("'", "''", $this->helperData->getValue($value));

        /**
         * Delete previously registered option with the identical same name
         */
        $storeId = 0;
        $this->queries[$this->queryIndexer][] = "SELECT @option_id:=IFNULL(" . "(SELECT cpbov.option_id FROM "
            . $this->tableCpbov . " AS cpbov WHERE title='" . $title . "' AND store_id = " . $storeId . " LIMIT 1)" . ", 0);";

        // catalog_product_relation
        $this->queries[$this->queryIndexer][] = "DELETE FROM " . $this->tableCpr . " WHERE parent_id = " . $productId . " AND child_id IN ("
            . "SELECT cpbs.product_id FROM " . $this->tableCpbs . " AS cpbs WHERE parent_product_id = " . $productId . " AND option_id = @option_id);";

        // catalog_product_bundle_selection
        $this->queries[$this->queryIndexer][] = "DELETE FROM " . $this->tableCpbs . " WHERE parent_product_id = "
            . $productId . " AND option_id = @option_id;";

        // catalog_product_bundle_option
        $this->queries[$this->queryIndexer][] = "DELETE FROM " . $this->tableCpbo . " WHERE parent_id = " . $productId
            . " AND option_id = @option_id;";

        // catalog_product_bundle_option_value
        $this->queries[$this->queryIndexer][] = "DELETE FROM " . $this->tableCpbov . " WHERE parent_product_id = " . $productId
            . " AND title='" . $title . "';";


        $remove = false;
        switch ($type) {
            case 'add_update':
                $fields = ['parent_id' => new \Zend_Db_Expr($productId), 'required' => 1, 'option_position' => 1, 'type' => 'select'];
                $preparedData = $this->helperData->prepareFields($fields, $value);

                // option_position is used for the position column as the position parameter can also be used for the first product (catalog_product_bundle_selection)
                // e.g: [type=dropdown|required=1|option_position=2|sku=24-MB01|selection_qty=2|is_default=0|position=4]
                $preparedData['position'] = $preparedData['option_position'];
                unset($preparedData['option_position']);
                break;
            case 'remove':
                $remove = true;
                break;
        }

        if (!$remove) {
            $attributes = $this->helperData->getJsonAttributes();
            $storeviews = $this->helperData->prepareStorewiewsParameters($attributes);

            $translations = $this->helperData->prepareFields($storeviews, $value);


            /**
             * Insert option base values - catalog_product_bundle_option (option_id / parent_id / required / position / type)
             */
            $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpbo, $preparedData);
            $this->queries[$this->queryIndexer][] = "SELECT @option_id:= LAST_INSERT_ID();";

            /**
             * Insert option title - catalog_product_bundle_option_value (value_id / option_id / store_id / title / parent_product_id)
             */
            $fields = ['option_id' => new \Zend_Db_Expr("@option_id"), 'store_id' => 0];


            $preparedData = $this->helperData->prepareFields($fields, $value);
            $preparedData['title'] = "'" . $title . "'";
            $preparedData['parent_product_id'] = $productId;


            $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpbov, $preparedData);
            foreach ($this->helperData->getStoreviews($attributes) as $storeview) {
                if ($translations[$storeview["code"]] != "''") {
                    $preparedData['store_id'] = $storeview["value"];
                    $preparedData['title'] = $translations[$storeview["code"]];
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpbov, $preparedData);
                }
            }


            /**
             * Insert bundle options product selection - catalog_product_bundle_selection
             * selection_id / option_id / parent_product_id / product_id / position / is_default / selection_price_type / selection_price_value / selection_qty / selection_can_change_qty
             */
            $groups = $this->helperData->prepareFields($fields, $value, null, true);

            foreach ($groups as $group) {
                $fields = [
                    'option_id' => new \Zend_Db_Expr("@option_id"),
                    'parent_product_id' => new \Zend_Db_Expr($productId),
                    'sku' => null,
                    'position' => 1,
                    'is_default' => 0,
                    'selection_price_type' => 0,
                    'selection_price_value' => 0,
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 0
                ];
                $preparedData = $this->helperData->prepareFields($fields, $group);
                $preparedData['product_id'] = "(SELECT cpe.entity_id FROM `" . $this->tableCpe . "` AS cpe WHERE cpe.sku=" . $preparedData['sku'] . " LIMIT 1)";
                unset($preparedData['sku']);

                $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpbs, $preparedData);

                /**
                 * Insert product relation - catalog_product_relation
                 * parent_id / child_id
                 */
                $productRelationData = [
                    'parent_id' => new \Zend_Db_Expr($productId),
                    'child_id' => new \Zend_Db_Expr($preparedData['product_id'])
                ];
                $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpr, $productRelationData);
            }
        }

        return parent::collect($productId, $value, $strategy, $profile);
    }

    /**
     * List of new mapping attributes
     * @return array
     */
    public function getDropdown()
    {
        $i = 0;
        $dropdown['Bundle Products'][$i]['label'] = __('Add/update an option');
        $dropdown['Bundle Products'][$i]['id'] = 'BundleProduct/add_update';
        $dropdown['Bundle Products'][$i]['style'] = '';
        $dropdown['Bundle Products'][$i]['type'] = __('Bundle items option title and option values separated by |');
        $dropdown['Bundle Products'][$i]['value'] = 'Bundle items option title to add/update '
            . '[type=dropdown|required=1|option_position=1|sku=SKU ABC|selection_qty=1|is_default=0|selection_can_change_qty=0]'
            . '[sku=SKU XYZ|selection_qty=2|is_default=1|selection_can_change_qty=0|position=1]';
        $i++;

        $dropdown['Bundle Products'][$i]['label'] = __('Remove an option');
        $dropdown['Bundle Products'][$i]['id'] = 'BundleProduct/remove';
        $dropdown['Bundle Products'][$i]['style'] = '';
        $dropdown['Bundle Products'][$i]['type'] = __('Bundle items option title');
        $dropdown['Bundle Products'][$i]['value'] = __('Option title');

        return $dropdown;
    }
}
