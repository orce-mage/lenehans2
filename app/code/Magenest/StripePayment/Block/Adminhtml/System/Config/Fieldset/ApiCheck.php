<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Block\Adminhtml\System\Config\Fieldset;

class ApiCheck extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = (string)$this->checkApi();

        if ($html) {
            $html = '<strong style="color:#006400;">' . $html . '</strong>';
            if (version_compare(\Stripe\Stripe::VERSION, "7.0.0") < 0) {
                $html.= '
                        <br>
                        <strong style="color:#e02b27;">'.__("Error: Stripe PHP Library was outdated").'</strong>
                        <p><small>'.__("Command to update: composer require stripe/stripe-php").'</small></p>
                        ';
            }
        } else {
            $html = '
                    <strong style="color:#e02b27;">'.__("Error: Stripe PHP Library was not installed correctly").'</strong>
                    <p><small>'.__("Command to install: composer require stripe/stripe-php").'</small></p>
                    ';
        }

        return $html;
    }

    protected function checkApi()
    {
        if (class_exists(\Stripe\Stripe::class)) {
            return __("Stripe PHP Library %1 was installed", \Stripe\Stripe::VERSION);
        } else {
            return false;
        }
    }
}
