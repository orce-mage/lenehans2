<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Model\ResourceModel\Db;

abstract class AbstractDb extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Module where the action is triggered
     * @var string
     */
    protected $module = 'Framework';

    /**
     * Entity where the action is triggered
     * @var string
     */
    protected $entity = null;

    /**
     * Fields not to check with dataHasChangedFor($field)
     * @var array
     */
    protected $fieldsNotToCheck = [];

    /**
     * @var \Wyomind\Framework\Helper\History
     */
    protected $helperHistory;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Wyomind\Framework\Helper\History $helperHistory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Wyomind\Framework\Helper\History $helperHistory,
        $connectionName = null
    ) {
    
        $this->helperHistory = $helperHistory;
        parent::__construct($context, $connectionName);
    }

    /**
     * Create a history of the updates/take picture of the entity after his creation or an update
     * {@inheritDoc}
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $table = $this->getMainTable();
        $columnNames = array_keys($this->getConnection()->describeTable($table));

        // Get DB table column comment
        $query = $this->getConnection()->query('SHOW FULL COLUMNS FROM ' . $table . ';');
        $tableDetails = $query->fetchAll();

        $columnComments = [];
        foreach ($tableDetails as $columnDetails) {
            if (array_key_exists('Comment', $columnDetails) && !empty($columnDetails['Comment'])) {
                $columnComments[$columnDetails['Field']] = $columnDetails['Comment'];
            } else {
                $columnComments[$columnDetails['Field']] = $columnDetails['Field'];
            }
        }

        // Fields to check
        $fields = array_diff($columnNames, $this->getFieldsNotToCheck());
        $updates = [];

        // Compare object data with original data
        foreach ($fields as $field) {
            if ($object->dataHasChangedFor($field)) {
                if (array_key_exists($field, $columnComments)) {
                    $updates[$columnComments[$field]] = [
                        'original' => $object->getOrigData($field),
                        'current' => $object->getData($field)
                    ];
                }
            }
        }

        // Get action origin & username
        $actionDetails = $this->getActionDetails();
        $actionType = 'update';

        if ($object->isObjectNew()) {
            $actionType = 'create';
        } else {
            if (1 !== $actionDetails['origin'] || $object->getData('action_type_history') === 'generate') {
                $actionType = 'generate';
            }
        }

        // Entity primary key
        $entityIdField = $object->getIdFieldName();
        $entityId = $object->getData($entityIdField);

        $module = $this->getModule();
        $entity = $this->getEntity();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $latestVersionId = $this->helperHistory->getLatestVersion($table, $entityId);

        // Add a line module_version_history
        if (!empty($updates)) {
            /** @var \Wyomind\Framework\Api\Data\VersionHistoryInterface $versionHistory */
            $versionHistory = $objectManager->create('Wyomind\\' . $module . '\Model' . ($entity ? '\\' . $entity : '') . '\VersionHistory');
            $latestVersionId++;

            $versionHistory->setEntityId($entityId);
            $versionHistory->setVersionId($latestVersionId);
            $versionHistory->setUsername($actionDetails['username']);

            // Remove fields like 'form_key'
            $fieldsToVersioned = array_intersect(array_keys($object->getData()), $fields);
            $content = array_intersect_key($object->getData(), array_flip($fieldsToVersioned));

            $serialize = "serialize";
            $versionHistory->setContent($serialize($content));
            $versionHistory->save();
        }

        // Add a line module_action_history
        $this->addActionHistory($entityId, $actionType, $updates);

        return $this;
    }

    /**
     * Create history of the delete action
     * {@inheritDoc}
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        // Entity primary key
        $entityIdField = $object->getIdFieldName();
        $entityId = $object->getData($entityIdField);

        $this->addActionHistory($entityId, 'delete');

        return $this;
    }

    /**
     * Add a line in module_action_history
     * @param string $entityId Entity primary key
     * @param string $actionType create, update, delete
     * @param array $updates
     */
    public function addActionHistory($entityId, $actionType, $updates = [])
    {
        if (!empty($updates) || (empty($updates) && $actionType !== 'update')) {
            $table = $this->getMainTable();

            // Get action origin & username
            $actionDetails = $this->getActionDetails();

            $module = $this->getModule();
            $entity = $this->getEntity();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $latestVersionId = $this->helperHistory->getLatestVersion($table, $entityId);

            /** @var \Wyomind\Framework\Api\Data\ActionHistoryInterface $actionHistory */
            $actionHistory = $objectManager->create('Wyomind\\' . $module . '\Model' . ($entity ? '\\' . $entity : '') . '\ActionHistory');
            $actionHistory->setEntityId($entityId);
            $actionHistory->setVersionId($latestVersionId);
            $actionHistory->setActionType($actionType);
            $actionHistory->setOrigin($actionDetails['origin']);
            $actionHistory->setUsername($actionDetails['username']);
            // @todo result
            // @todo message
            $serialize = "serialize";
            $actionHistory->setDetails($serialize($updates));

            $actionHistory->save();
        }
    }

    /**
     * Get action origin & associated username
     * @return array
     */
    public function getActionDetails()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('\Magento\Framework\App\RequestInterface');
        $action = ['origin' => null, 'username' => null];

        if (isset($request->getParams()['key'])) {
            // Action triggered from the backend
            $action['origin'] = \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_BACKEND;

            $auth = $objectManager->get('\Magento\Backend\Model\Auth');
            if ($auth->getUser() != null) {
                $action['username'] = $auth->getUser()->getUsername();
            } else {
                $action['username'] = __('Unknown user');
            }
        } else {
            if (php_sapi_name() == 'cli') {
                // Action triggered from CLI
                $action['origin'] = \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_CLI;
                $action['username'] = utf8_encode(get_current_user());
            } else {
                $state = $objectManager->get('\Magento\Framework\App\State');
                $area = "";
                try {
                    $area = $state->getAreaCode();
                } catch (\Exception $e) {
                }
                if ($area == 'webapi_rest') {
                    // Action triggered from API
                    $action['origin'] = \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_API;
                } else {
                    // Action triggered from Cron
                    $action['origin'] = \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_CRON;
                    $action['username'] = utf8_encode(get_current_user());
                }
            }
        }

        return $action;
    }

    /**
     * Returns module
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Returns entity
     * @return string|null
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Returns fieldsNotToCheck
     * @return array
     */
    public function getFieldsNotToCheck()
    {
        return $this->fieldsNotToCheck;
    }
}
