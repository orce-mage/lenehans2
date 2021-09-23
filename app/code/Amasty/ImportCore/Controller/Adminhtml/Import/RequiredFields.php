<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Controller\Adminhtml\Import;

use Amasty\ImportCore\Import\Form\Fields\RequiredFieldsProvider;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class RequiredFields extends \Magento\Backend\App\Action
{
    /**
     * @var RequiredFieldsProvider
     */
    private $requiredFieldsProvider;

    public function __construct(
        Context $context,
        RequiredFieldsProvider $requiredFieldsProvider
    ) {
        parent::__construct($context);
        $this->requiredFieldsProvider = $requiredFieldsProvider;
    }

    public function execute()
    {
        $result = [];
        if ($entityCode = $this->getRequest()->getParam('entity_code')) {
            $result = $this->requiredFieldsProvider->get(
                $entityCode,
                $this->getRequest()->getParam('behavior_code'),
                $this->getRequest()->getParam('identifier')
            );
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }
}
