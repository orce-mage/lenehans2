<?php

namespace Swissup\SoldTogether\Api\Data;

interface EntityInterface
{
    const RELATION_ID  = 'relation_id';
    const PRODUCT_ID   = 'product_id';
    const RELATED_ID   = 'related_id';
    const STORE_ID     = 'store_id';
    const PRODUCT_NAME = 'product_name';
    const RELATED_NAME = 'related_name';
    const WEIGHT       = 'weight';
    const IS_ADMIN     = 'is_admin';

    /**
     * Get relation_id
     *
     * return int
     */
    public function getRelationId();

    /**
     * Get product_id
     *
     * return int
     */
    public function getProductId();

    /**
     * Get related_id
     *
     * return int
     */
    public function getRelatedId();

    /**
     * Get store_id
     *
     * return int
     */
    public function getStoreId();

    /**
     * Get product_name
     *
     * return string
     */
    public function getProductName();

    /**
     * Get related_name
     *
     * return string
     */
    public function getRelatedName();

    /**
     * Get weight
     *
     * return int
     */
    public function getWeight();

    /**
     * Get is_admin
     *
     * return int
     */
    public function getIsAdmin();

    /**
     * Set relation_id
     *
     * @param int $relationId
     * return \Swissup\SoldTogether\Api\Data\EntityInterface
     */
    public function setRelationId($relationId);

    /**
     * Set product_id
     *
     * @param int $productId
     * return \Swissup\SoldTogether\Api\Data\EntityInterface
     */
    public function setProductId($productId);

    /**
     * Set related_id
     *
     * @param int $relatedId
     * return \Swissup\SoldTogether\Api\Data\EntityInterface
     */
    public function setRelatedId($relatedId);

    /**
     * Set store_id
     *
     * @param int $storeId
     * return \Swissup\SoldTogether\Api\Data\EntityInterface
     */
    public function setStoreId($storeId);

    /**
     * Set product_name
     *
     * @param string $productName
     * return \Swissup\SoldTogether\Api\Data\EntityInterface
     */
    public function setProductName($productName);

    /**
     * Set related_name
     *
     * @param string $relatedName
     * return \Swissup\SoldTogether\Api\Data\EntityInterface
     */
    public function setRelatedName($relatedName);

    /**
     * Set weight
     *
     * @param int $weight
     * return \Swissup\SoldTogether\Api\Data\EntityInterface
     */
    public function setWeight($weight);

    /**
     * Set is_admin
     *
     * @param int $isAdmin
     * return \Swissup\SoldTogether\Api\Data\EntityInterface
     */
    public function setIsAdmin($isAdmin);
}
