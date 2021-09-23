<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Controller\Adminhtml\History;

abstract class ActionHistory extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
    
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize action by ID specified in request
     * @return $this
     */
    public function initCurrentAction()
    {
        $currentId = (int)$this->getRequest()->getParam('current_entity_id');
        if ($currentId) {
            $this->coreRegistry->register('current_entity_id', $currentId);
        }

        return $this;
    }
}
