<?php

namespace Swissup\CheckoutSuccess\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Preview extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Data\Form\ElementFactory
     */
    protected $elementFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var string
     */
    protected $previewHtmlId = 'row_success_page_layout_preview_iframe';

     /**
     * @param \Magento\Framework\Data\Form\ElementFactory $elementFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\ElementFactory $elementFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        return parent::__construct($context, $data);
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return parent::render($element)
            . '<tr id="' . $this->previewHtmlId . '">'
            . '<td colspan="3" class="value">'
            . '<div data-role="iframe-placeholder" style="display: none;">'
            . '<div data-role="spinner" class="admin__data-grid-loading-mask">'
            . '<div class="spinner">'
            . '<span></span><span></span><span></span><span></span>'
            . '<span></span><span></span><span></span><span></span>'
            . '</div>'
            . '</div>'
            . '</div>'
            . '</td>'
            . '</tr>';
    }

    /**
     * Render element HTML
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        // add button to start preview
        $button = $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Button')
            ->setLabel(__('Save and Start Preview'))
            ->setId('success_page__start_and_preview')
            ->setDataAttribute([
                'mage-init' => [
                    'Swissup_CheckoutSuccess/js/success-page/preview' => [
                        'target' => '#'.$this->previewHtmlId.' td',
                        'previewUrl' => $this->getPreviewUrl(),
                        'source' => '#row_'.$element->getHtmlId().' td.value'
                    ]
                ]
            ]);

        $element->setName(''); // remove name to prevent saving to config
        $element->setValue($this->getLastOrder()->getIncrementId());
        return parent::_getElementHtml($element)
            . $button->toHtml()
            ;
    }

    /**
     * Check if inheritance checkbox has to be rendered
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return bool
     */
    protected function _isInheritCheckboxRequired(AbstractElement $element)
    {
        return false;
    }

    /**
     * Get URL to preview Checkout Success Page
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        $store = $this->getStoreInConfig();
        $url = [
            'checkout',
            'onepage',
            'success',
            'previewObjectId',
            '{{orderNumber}}',
            'hash',
            '{{previewHash}}'
        ];
        return $store->getBaseUrl()
            . implode('/', $url)
            . '/?___store=' . $store->getCode();
    }

    public function getStoreInConfig()
    {
        $form = $this->getForm();
        if ($form->getStoreCode()) {
            $store = $this->_storeManager->getStore($form->getStoreCode());
        } elseif ($form->getWebsiteCode()) {
            $store = $this->_storeManager->getWebsite($form->getWebsiteCode())
                ->getDefaultStore();
        } else {
            $store = $this->_storeManager->getDefaultStoreView();
        }

        return $store;
    }

    public function getLastOrder()
    {
        $store = $this->getStoreInConfig();
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('store_id', $store->getId())
            ->setOrder('entity_id', 'DESC')
            ->setPageSize(1)
            ->setCurPage(1);
        return $collection->getFirstItem();
    }
}
