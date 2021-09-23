<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Block\Adminhtml\Conditions\CodePool;

use Amasty\GiftCard\Model\CodePool\CodePoolRule;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Rule\Model\Condition\AbstractCondition;

class Conditions extends Generic
{
    /**
     * Block name in layout
     *
     * @var string
     */
    protected $_nameInLayout = 'conditions';

    /**
     * @var CodePoolRule
     */
    private $rule;

    /**
     * @var \Magento\Rule\Block\ConditionsFactory
     */
    private $conditionsFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\FieldsetFactory
     */
    private $fieldsetFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Rule\Block\ConditionsFactory $conditionsFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Block\Widget\Form\Renderer\FieldsetFactory $fieldsetFactory,
        array $data = []
    ) {
        $this->rule = $registry->registry(CodePoolRule::CURRENT_RULE);
        parent::__construct($context, $registry, $formFactory, $data);
        $this->conditionsFactory = $conditionsFactory;
        $this->formFactory = $formFactory;
        $this->fieldsetFactory = $fieldsetFactory;
    }

    public function _toHtml()
    {
        $conditionsFieldSetId = CodePoolRule::FORM_NAMESPACE
            . 'rule_conditions_fieldset';
        $newChildUrl = $this->getUrl(
            'amgcard/codepool/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => CodePoolRule::FORM_NAMESPACE]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formFactory->create();
        $renderer = $this->fieldsetFactory->create()->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);
        $fieldset = $form->addFieldset(
            $conditionsFieldSetId,
            [
                'legend' => __("Conditions (don't specify conditions if you'd the"
                    . " like rule to be applied to all products)")
            ]
        )->addClass(
            'fieldset am-condition-fieldset'
        )->setRenderer(
            $renderer
        );
        $fieldset->addField(
            'conditions' . $conditionsFieldSetId,
            'text',
            [
                'name' => 'conditions' . $conditionsFieldSetId,
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'data-form-part' => CodePoolRule::FORM_NAMESPACE,
            ]
        )->setRule($this->rule)->setRenderer($this->conditionsFactory->create());
        $form->setValues($this->rule->getData());
        $this->setConditionFormName($this->rule->getConditions(), CodePoolRule::FORM_NAMESPACE);

        return $form->toHtml();
    }

    /**
     * @param AbstractCondition $abstractConditions
     * @param string $formName
     *
     * @return void
     */
    private function setConditionFormName(AbstractCondition $abstractConditions, string $formName)
    {
        $fieldsetId = CodePoolRule::FORM_NAMESPACE . 'rule_conditions_fieldset';
        $abstractConditions->setFormName($formName);
        $abstractConditions->setJsFormObject($fieldsetId);
        $conditions = $abstractConditions->getConditions();

        if ($conditions && is_array($conditions)) {
            foreach ($conditions as $condition) {
                $this->setConditionFormName($condition, $formName);
                $condition->setJsFormObject($fieldsetId);
            }
        }
    }
}
