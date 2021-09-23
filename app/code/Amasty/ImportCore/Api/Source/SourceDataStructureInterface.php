<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Source;

interface SourceDataStructureInterface
{
    /**
     * Get entity code
     *
     * @return string
     */
    public function getEntityCode();

    /**
     * Get input map/prefix
     *
     * @return string|null
     */
    public function getMap();

    /**
     * Get input entity field names
     *
     * @return string[]
     */
    public function getFields();

    /**
     * Returns import entity field name (before mapping) using expected input field name
     *
     * @param string $field
     * @return string|bool
     */
    public function getFieldName($field);

    /**
     * Get input entity Id field name
     *
     * @return string|null
     */
    public function getIdFieldName();

    /**
     * Get input entity parent FK field name.
     *
     * @return string|null
     */
    public function getParentIdFieldName();

    /**
     * Get sub entities structures (keyed by input map/prefix)
     *
     * @return \Amasty\ImportCore\Api\Source\SourceDataStructureInterface[]
     */
    public function getSubEntityStructures();
}
