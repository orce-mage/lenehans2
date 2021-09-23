<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Magetrend\PdfTemplates\Block\Adminhtml\Template\Edit\Tab;

use \Magento\Backend\Block\Widget\Form\Generic;
use \Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Template general information tab block
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class General extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $systemStore;

    /**
     * @var \Magetrend\PdfTemplates\Model\Config\Source\OrderObjectList
     */
    public $orderObjectList;
    
    /**
     * @var array|\Magetrend\PdfTemplates\Helper\Data
     */
    public $helper;

    /**
     * General constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magetrend\PdfTemplates\Model\Config\Source\OrderObjectList $orderObjectList
     * @param \Magetrend\PdfTemplates\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magetrend\PdfTemplates\Helper\Data $helper,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    //@codingStandardsIgnoreLine
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_object');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Settings')]);

        if ($model->getId()) {
            $fieldset->addField(
                'entity_id',
                'hidden',
                [
                    'name' => 'entity_id',
                    'value' => ''
                ]
            );
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Template Title'),
                'title' => __('Template Title'),
                'required' => true,
                'disabled' => false
            ]
        );
        $fieldset->addField(
            'store_ids',
            'multiselect',
            [
                'name' => 'store_ids[]',
                'label' => __('Show in Stores'),
                'title' => __('Show in Stores'),
                'required' => true,
                'values' => $this->systemStore->getStoreValuesForForm(false, true),
                'value' => 0,
            ]
        );

        $fieldset->addField(
            'type',
            'multiselect',
            [
                'name' => 'type[]',
                'label' => __('Available on types'),
                'title' => __('Available on types'),
                'required' => true, 'value' => 0,
            ]
        );

        if ($model->getId()) {
            $data = $model->getData();
            $data['type'] = $this->helper->decodeArray($model->getType());
            $data['store_ids'] = $this->helper->decodeArray($model->getStoreIds());
            $form->setValues($data);
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General Settings');
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
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
