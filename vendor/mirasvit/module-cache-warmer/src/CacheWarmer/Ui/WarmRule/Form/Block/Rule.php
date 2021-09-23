<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Ui\WarmRule\Form\Block;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as FieldsetRenderer;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions;
use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;

class Rule extends Form implements TabInterface
{
    /**
     * @var FieldsetRenderer
     */
    private $fieldsetRenderer;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditions;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var \Magento\Backend\Model\Url
     */
    private $urlManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    private $context;

    /**
     * @var string
     */
    protected $_nameInLayout = 'conditions_serialized';

    /**
     * Rule constructor.
     * @param Conditions $conditions
     * @param FieldsetRenderer $fieldsetRenderer
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        Conditions $conditions,
        FieldsetRenderer $fieldsetRenderer,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->fieldsetRenderer = $fieldsetRenderer;
        $this->conditions       = $conditions;
        $this->formFactory      = $formFactory;
        $this->registry         = $registry;
        $this->context          = $context;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $formName = \Mirasvit\CacheWarmer\Model\WarmRule\Rule::FORM_NAME;

        /** @var WarmRuleInterface $WarmRule */
        $WarmRule = $this->registry->registry(WarmRuleInterface::class);
        $rule    = $WarmRule->getRule();

        $form = $this->formFactory->create();
        $form->setData('html_id_prefix', 'rule_');

        $fieldsetName = 'conditions_fieldset';

        $renderer = $this->fieldsetRenderer
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setData('new_child_url', $this->getUrl('*/WarmRule/newConditionHtml', [
                'form'      => 'rule_' . $fieldsetName,
                'form_name' => $formName,
            ]));

        $fieldset = $form->addFieldset($fieldsetName, [
            'legend' => __('Conditions (leave blank for all pages)'),
        ])->setRenderer($renderer);

        $rule->getConditions()
            ->setRuleType($WarmRule->getRuleType())
            ->setFormName($formName);

        $conditionsField = $fieldset->addField('conditions', 'text', [
            'name'           => 'conditions',
            'required'       => true,
            'data-form-part' => $formName,
        ]);

        $conditionsField->setRule($rule)
            ->setRenderer($this->conditions)
            ->setFormName($formName);

        $form->setValues($WarmRule->getData());
        $this->setConditionFormName($rule->getConditions(), $formName);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param object $conditions
     * @param string $formName
     * @return void
     */
    private function setConditionFormName($conditions, $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }
}
