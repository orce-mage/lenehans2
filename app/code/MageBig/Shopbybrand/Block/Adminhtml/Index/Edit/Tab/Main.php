<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Block\Adminhtml\Index\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Theme\Helper\Storage;

class Main extends Generic implements TabInterface
{

    public function getTabLabel()
    {
        return __('General');
    }

    public function getTabTitle()
    {
        return __('General');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('brand');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('brand_');
        $scopeConfig = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
        if ($model->getEntityId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        } else {
            $model->addData([
                'is_active' => 1,
                'mb_brand_is_featured' => 1
            ]);
        }

        $fieldset->addField('store', 'hidden', ['name' => 'store']);

        if ($model->getOptionId()) {
            $fieldset->addField('option_id', 'hidden', ['name' => 'option_id']);
        }
        $fieldset->addField(
            'brand_label',
            'label',
            ['name' => 'brand_label', 'label' => __('Brand Label'), 'title' => __('Brand Label')]
        );

        $fieldset->addField(
            'is_active',
            'select',
            ['name' => 'is_active', 'label' => __('Active'), 'title' => __('Active'),
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $field = $fieldset->addField(
            'mb_brand_thumbnail',
            'hidden',
            ['name' => 'mb_brand_thumbnail', 'label' => __('Logo'), 'title' => __('Logo'), 'required' => false, 'class' => 'input-image', 'onchange' => 'changePreviewImage(this)']
        );

        $fieldset->addField(
            'mb_brand_is_featured',
            'select',
            ['name' => 'mb_brand_is_featured', 'label' => __('Is Featured'), 'title' => __('Is Featured'),
                'required' => false,
                'options' => ['0' => __('No'), '1' => __('Yes')],
                'value' => 0
            ]
        );

        $fieldset->addField(
            'mb_brand_description',
            'editor',
            ['name' => 'mb_brand_description', 'config' => $this->getWysiwygConfig(), 'label' => __('Description'), 'title' => __('Description'), 'required' => false]
        );

        $renderer = $this->getLayout()->createBlock(
            'MageBig\Shopbybrand\Block\Adminhtml\Shopbybrand\AbstractHtmlField\Image'
        );

        //$field = $fieldset->addField(
        //    'mb_brand_cover',
        //    'hidden',
        //    ['name' => 'mb_brand_cover', 'label' => __('Cover Image'), 'title' => __('Cover Image'), 'required' => false, 'class' => 'input-image', 'onchange' => 'changePreviewImage(this)']
        //);

        $fieldset = $form->addFieldset(
            'base_fieldset_search',
            ['legend' => __('Search Engine Optimization'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'mb_brand_url_key',
            'text',
            ['name' => 'mb_brand_url_key', 'label' => __('URL Key'), 'title' => __('URL Key'), 'required' => false]
        );

        $fieldset->addField(
            'mb_brand_meta_title',
            'text',
            ['name' => 'mb_brand_meta_title', 'label' => __('Meta Title'), 'title' => __('Meta Title'), 'required' => false]
        );

        $fieldset->addField(
            'mb_brand_meta_description',
            'textarea',
            ['name' => 'mb_brand_meta_description', 'label' => __('Meta Description'), 'title' => __('Meta Description'), 'required' => false]
        );

        $fieldset->addField(
            'mb_brand_meta_keyword',
            'text',
            ['name' => 'mb_brand_meta_keyword', 'label' => __('Meta Keyword'), 'title' => __('Meta Keyword'), 'required' => false]
        );

        $field->setRenderer($renderer);

        $form->setDataObject($model);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getWysiwygConfig()
    {
        $config = [];
        $config['container_class'] = 'hor-scroll';
        $wysiwygConfig = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Cms\Model\Wysiwyg\Config');
        return $wysiwygConfig->getConfig($config);
    }
}
