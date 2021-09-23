<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/Email
 * @author   Edvinas St <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-email-templates
 */

namespace Magetrend\Email\Block\Adminhtml\System\Form\Field;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;

/**
 * Tracking links
 */
class Track extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    private $shippingMethod = null;

    /**
     * Prepare to render.
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'shipping_method',
            [
                'label' => __('Shipping Method'),
                'renderer' => $this->getShippingMethodRenderer()
            ]
        );

        $this->addColumn(
            'track',
            [
                'label' => __('Tracking Link'),
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    public function getShippingMethodRenderer()
    {
        if ($this->shippingMethod === null) {
            $this->shippingMethod = $this->getLayout()->createBlock(
                '\Magetrend\Email\Block\Adminhtml\System\Form\Field\ShippingMethod',
                '',
                ['data' => ['is_render_to_js_template' => true, 'class' => 'shipping-method-field']]
            );
        }

        return $this->shippingMethod;
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
        $shippingMethod = $row->getData('shipping_method');

        $key = 'option_' . $this->getShippingMethodRenderer()->calcOptionHash($shippingMethod);
        $options[$key] = 'selected="selected"';


        $row->setData('option_extra_attrs', $options);
    }
}