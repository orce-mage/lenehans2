<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Block\Adminhtml\Rule\Edit;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Api\RuleRepositoryInterface;
use Amasty\Stockstatus\Model\Rule\Condition;
use Amasty\Stockstatus\Model\Rule\ConditionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions as ConditionsBlock;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class Conditions extends Generic implements TabInterface
{
    /**
     * @var Fieldset
     */
    private $rendererFieldset;

    /**
     * @var ConditionsBlock
     */
    private $conditions;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var ConditionFactory
     */
    private $conditionFactory;

    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        ConditionFactory $conditionFactory,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ConditionsBlock $conditions,
        Fieldset $rendererFieldset,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->conditions = $conditions;
        $this->rendererFieldset = $rendererFieldset;
        $this->ruleRepository = $ruleRepository;
        $this->conditionFactory = $conditionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var Condition $conditionRule */
        $conditionRule = $this->conditionFactory->create();
        $ruleId = (int) $this->_request->getParam(RuleInterface::ID);

        if ($ruleId) {
            try {
                $rule = $this->ruleRepository->getById($ruleId);
                $conditionRule->setConditionsSerialized($rule->getConditionsSerialized());
            } catch (NoSuchEntityException $e) {
                null;
            }
        }

        $form = $this->addTabToForm($conditionRule);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function addTabToForm(
        Rule $model,
        string $fieldsetId = 'conditions_fieldset',
        string $formName = 'amasty_stockstatus_rule_form'
    ): Form {
        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);
        $newChildUrl = $this->getUrl(
            'amstockstatus/conditions/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => $formName]
        );

        $renderer = $this->rendererFieldset
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __('Choose conditions to define products,
                for which will be displayed current custom stock status.')
            ]
        )->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true
            ]
        )->setRule($model)->setRenderer($this->conditions);

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getConditions(), $formName, $conditionsFieldSetId);

        return $form;
    }

    private function setConditionFormName(
        AbstractCondition $conditions,
        string $formName,
        string $jsFormName
    ): void {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }
}
