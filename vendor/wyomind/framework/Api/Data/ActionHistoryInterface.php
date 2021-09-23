<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Api\Data;

/**
 * Interface ActionHistoryInterface
 * @api
 */
interface ActionHistoryInterface
{
    const ID = 'action_id';
    const VERSION_ID = 'version_id';
    const ENTITY_ID = 'entity_id';
    const ACTION_TYPE = 'action_type';
    const ORIGIN = 'origin';
    const USERNAME = 'username';
    const RESULT = 'result';
    const MESSAGE = 'message';
    const DETAILS = 'details';
    const CREATED_AT = 'created_at';

    const ORIGIN_BACKEND = 1;
    const ORIGIN_CRON = 2;
    const ORIGIN_CLI = 3;
    const ORIGIN_API = 4;

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
     * Get the entity ID of the object on which the action is performed
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
     * Get the version related to the action
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
     * Get the action type (create / generate / update / delete / etc)
     * @return string
     */
    public function getActionType();

    /**
     * Set action type (create / generate / update / delete / etc)
     * @param string $actionType
     * @return $this
     */
    public function setActionType($actionType);

    /**
     * Get where the action has been triggered (1 = Backend, 2 = Cron, 3 = CLI, 4 = API)
     * @return int
     */
    public function getOrigin();

    /**
     * Set where the action has been triggered (1 = Backend, 2 = Cron, 3 = CLI, 4 = API)
     * @param int $origin
     * @return $this
     */
    public function setOrigin($origin);

    /**
     * Get the author of this action
     * @return string
     */
    public function getUsername();

    /**
     * Set the author of this action
     * @param string $username
     * @return $this
     */
    public function setUsername($username);

    /**
     * Get result (success / failed / error / etc)
     * @return string
     */
    public function getResult();

    /**
     * Set result (success / failed / error / etc)
     * @param string $result
     * @return $this
     */
    public function setResult($result);

    /**
     * Get result message
     * @return string
     */
    public function getMessage();

    /**
     * Set result message
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Get details
     * @return string
     */
    public function getDetails();

    /**
     * Set details
     * @param string $details
     * @return $this
     */
    public function setDetails($details);

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
