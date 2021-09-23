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

namespace Magetrend\PdfTemplates\Block\Adminhtml;

/**
 * Template management container
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Template extends \Magento\Backend\Block\Widget\Grid\Container
{
    public $moduleHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        array $data = []
    ) {
        $this->moduleHelper = $moduleHelper;
        parent::__construct($context, $data);
    }
    /**
     * Set page attributes
     *
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        $this->_controller = 'template_index';
        $this->_headerText = __('Manage Templates');
        return parent::_construct();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->removeButton('add');
        $this->addButton('add', [
            'id' => 'add_new_template',
            'label' => '&nbsp;'.__('Create New Template').'&nbsp;&nbsp;&nbsp;&nbsp;',
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->getAddButtonOptions(),
        ]);

        return parent::_prepareLayout();
    }

    /**
     * Buttons option
     *
     * @return array
     */
    public function getAddButtonOptions()
    {
        $config = [[
            'label' => __('Order'),
            'onclick' => "setLocation('" . $this->getNewEntityUrl('order') . "')"
        ],[
            'label' => __('Invoice'),
            'onclick' => "setLocation('" . $this->getNewEntityUrl('invoice') . "')"
        ],[
            'label' => __('Shipment'),
            'onclick' => "setLocation('" . $this->getNewEntityUrl('shipment') . "')"
        ],[
            'label' => __('Credit Memo'),
            'onclick' => "setLocation('" . $this->getNewEntityUrl('creditmemo') . "')"
        ]];

        if ($this->moduleHelper->isQuoteEnabled()) {
            $config[] = [
                'label' => __('Quote'),
                'onclick' => "setLocation('" . $this->getNewEntityUrl('quote') . "')"
            ];
        }

        return $config;
    }

    /**
     * New template url
     *
     * @param $key
     * @return string
     */
    public function getNewEntityUrl($key)
    {
        return $this->getUrl(
            '*/mteditor/index',
            ['type' => $key]
        );
    }
}
