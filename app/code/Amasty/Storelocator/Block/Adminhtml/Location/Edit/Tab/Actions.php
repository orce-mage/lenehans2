<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Location\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Condition for locations configuration.
 */
class Actions extends Generic implements TabInterface
{
    const FORM_NAME = 'amstorelocator_location_form';

    const RULE_ACTIONS_FIELDSET_NAMESPACE = 'rule_actions_fieldset';

    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $actions;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Actions $actions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->rendererFieldset = $rendererFieldset;
        $this->actions = $actions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Available Products');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Available Products');
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
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amasty_storelocator_location');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $renderer = $this->rendererFieldset
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setFieldSetId(self::RULE_ACTIONS_FIELDSET_NAMESPACE)
            ->setNewChildUrl(
                $this->getUrl(
                    '*/*/newActionHtml',
                    ['form_namespace' => self::FORM_NAME, 'form' => self::RULE_ACTIONS_FIELDSET_NAMESPACE]
                )
            );

        $fieldset = $form->addFieldset(
            self::RULE_ACTIONS_FIELDSET_NAMESPACE,
            [
                'legend' => __(
                    'Apply the rule only if the following conditions are met (leave blank for all products).'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name'  => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'data-form-part' => self::FORM_NAME,
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->actions
        );
        $this->setActionFormName($model->getActions(), self::RULE_ACTIONS_FIELDSET_NAMESPACE, self::FORM_NAME);
        $form->setValues($model->getData());
        $form->addValues(['id' => $model->getId()]);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param \Magento\Rule\Model\Condition\AbstractCondition $actions
     * @param string $fieldsetName
     * @param string $formName
     *
     * @return void
     */
    protected function setActionFormName(
        \Magento\Rule\Model\Condition\AbstractCondition $actions,
        $fieldsetName,
        $formName
    ) {
        $actions->setFormName($formName);
        $actions->setJsFormObject($fieldsetName);

        if ($actions->getActions() && is_array($actions->getActions())) {
            foreach ($actions->getActions() as $condition) {
                $this->setActionFormName($condition, $fieldsetName, $formName);
            }
        }
    }
}
