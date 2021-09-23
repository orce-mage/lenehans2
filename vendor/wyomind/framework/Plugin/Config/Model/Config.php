<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Plugin\Config\Model;

/**
 * Add log lines when modifying the license group of any extension
 */
class Config
{

    /**
     * {@inherit}
     */
    public $logger = null;




    /**
     * Config constructor.
     * @param \Wyomind\Framework\Helper\License $license
     */
    public function __construct(
        \Wyomind\Framework\Helper\License $license
    ) {
    
        $this->license = $license;


    }

    /**
     * Add a line in the log file
     * @param string $msg
     */
    public function notice($msg)
    {

            $this->license->getLogger()->notice($msg);

    }

    /**
     * Check the value of the configuration before saving them
     * @param type $subject
     */
    public function beforeSave($subject)
    {
        $groups = $subject->getGroups();
        if ($groups) {
            foreach ($groups as $groupId => $groupData) {
                $groupPath = $subject->getSection() . '/' . $groupId;
                if (isset($groupData['fields'])) {
                    foreach ($groupData['fields'] as $key => $values) {
                        $fullPath = $groupPath . "/" . $key;
                        if ($key == "activation_key") {
                            $this->notice("------------------------------------------");
                            $this->notice("Update in Stores > Configuration");
                            $this->notice("Activation key updated in config: " . $fullPath . " => " . implode(',', $values));
                            if ($this->license->isAdmin()) {
                                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                $auth = $objectManager->get("\Magento\Backend\Model\Auth");
                                if ($auth->getUser() != null) {
                                    $this->notice("User: " . $auth->getUser()->getUsername());
                                }
                            }
                        }
                        if ($key == "activation_code") {
                            $this->notice("------------------------------------------");
                            $this->notice("Update in Stores > Configuration");
                            $this->notice("License code updated in config: " . $fullPath . " => " . implode(',', $values));
                            if ($this->license->isAdmin()) {
                                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                $auth = $objectManager->get("\Magento\Backend\Model\Auth");
                                if ($auth->getUser() != null) {
                                    $this->notice("User: " . $auth->getUser()->getUsername());
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
