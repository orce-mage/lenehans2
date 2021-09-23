<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Api\Data;

/**
 * Interface VersionHistoryInterface
 * @api
 */
interface VersionHistoryInterface
{
    const ID = 'id';
    const ENTITY_ID = 'entity_id';
    const VERSION_ID = 'version_id';
    const USERNAME = 'username';
    const CONTENT = 'content';
    const CREATED_AT = 'created_at';

    /**
     * Get ID
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get the entity ID of the versioned object
     * @return int
     */
    public function getEntityId();

    /**
     * Set entity ID
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get the version number of the element logged
     * @return int
     */
    public function getVersionId();

    /**
     * Set version id
     * @param int $versionId
     * @return $this
     */
    public function setVersionId($versionId);

    /**
     * Get the author of this version
     * @return string
     */
    public function getUsername();

    /**
     * Set the author of this version
     * @param string $username
     * @return $this
     */
    public function setUsername($username);

    /**
     * Get the version content
     * @return string
     */
    public function getContent();

    /**
     * Set the version content
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Get creation time
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set creation time
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
}
