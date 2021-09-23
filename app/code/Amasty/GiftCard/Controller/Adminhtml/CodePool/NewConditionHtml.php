<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\CodePool;

use Amasty\GiftCard\Api\Data\CodePoolRuleInterfaceFactory;
use Amasty\GiftCard\Controller\Adminhtml\AbstractCodePool;
use Magento\Backend\App\Action;
use Magento\Rule\Model\Condition\AbstractCondition;

class NewConditionHtml extends AbstractCodePool
{
    const CONDITION_TYPE = 0;
    const CONDITION_ATTR = 1;
    /**
     * @var CodePoolRuleInterfaceFactory
     */
    private $ruleFactory;

    public function __construct(Action\Context $context, CodePoolRuleInterfaceFactory $ruleFactory)
    {
        parent::__construct($context);
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Generate Condition HTML form. Ajax
     *
     * @inheritDoc
     */
    public function execute()
    {
        //for condition id in formats 1--1, not format to int
        $conditionId = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getPost('type')));
        $type = $typeArr[self::CONDITION_TYPE];

        if (empty($type) || !is_subclass_of($type, AbstractCondition::class)) {
            return;
        }
        $model = $this->_objectManager->create($type)
            ->setId($conditionId)
            ->setType($type)
            ->setRule($this->ruleFactory->create())
            ->setPrefix('conditions');

        if (!empty($typeArr[self::CONDITION_ATTR])) {
            $model->setAttribute($typeArr[self::CONDITION_ATTR]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($this->getRequest()->getParam('form_namespace'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
