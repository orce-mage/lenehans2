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

namespace Magetrend\PdfTemplates\Block\Adminhtml\System\Form\Field;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;

/**
 * PDF - Customer Field class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
abstract class PdfCustomerMapAbstract extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    public $customerGroup;

    public $shippingMethod;

    abstract public function getTemplateRenderer();

    /**
     * Get activation options.
     */
    public function getCustomerGroupRenderer()
    {
        if (!$this->customerGroup) {
            $this->customerGroup = $this->getLayout()->createBlock(
                '\Magetrend\PdfTemplates\Block\Adminhtml\System\Form\Field\CustomerGroup',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->customerGroup;
    }

    public function getShippingMethodRenderer()
    {
        if (!$this->shippingMethod) {
            $this->shippingMethod = $this->getLayout()->createBlock(
                '\Magetrend\PdfTemplates\Block\Adminhtml\System\Form\Field\ShippingMethod',
                '',
                ['data' => ['is_render_to_js_template' => true, 'class' => 'shipping-method-field']]
            );
        }

        return $this->shippingMethod;
    }

    /**
     * Prepare to render.
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'customer_group',
            [
                'label' => __('Customer Group'),
                'renderer' => $this->getCustomerGroupRenderer()
            ]
        );

        $this->addColumn(
            'shipping_method',
            [
                'label' => __('Shipping Method'),
                'renderer' => $this->getShippingMethodRenderer()
            ]
        );

        $this->addColumn(
            'pdf_template',
            [
                'label' => __('PDF Template'),
                'renderer' => $this->getTemplateRenderer()
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object.
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        $customerAttribute = $row->getData('customer_group');
        $shippingMethod = $row->getData('shipping_method');
        $templateAttribute = $row->getData('pdf_template');

        $key = 'option_' . $this->getCustomerGroupRenderer()->calcOptionHash($customerAttribute);
        $options[$key] = 'selected="selected"';

        $ke3 = 'option_' . $this->getShippingMethodRenderer()->calcOptionHash($shippingMethod);
        $options[$ke3] = 'selected="selected"';

        $key2 = 'option_' . $this->getTemplateRenderer()->calcOptionHash($templateAttribute);
        $options[$key2] = 'selected="selected"';

        $row->setData('option_extra_attrs', $options);
    }
}