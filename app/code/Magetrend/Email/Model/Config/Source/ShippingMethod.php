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

namespace Magetrend\Email\Model\Config\Source;

use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Shipping method source class
 */
class ShippingMethod implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Shipping\Model\Config
     */
    public $shippingConfig;

    /**
     * ShippingMethod constructor.
     * @param \Magento\Shipping\Model\Config $shippingConfig
     */
    public function __construct(
        \Magento\Shipping\Model\Config $shippingConfig
    ) {
        $this->shippingConfig = $shippingConfig;
    }

    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $optionArray = $this->getCarriers();
        return $optionArray;
    }


    /**
     * Retrieve carriers
     *
     * @return array
     */
    public function getCarriers()
    {
        $carrierInstances = $this->getCarriersInstances();
        $carriers = [
            [
                'label' => __('Custom Value'),
                'value' => 'custom'
            ]
        ];

        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[] = [
                    'label' => $carrier->getConfigData('title'),
                    'value' => $code
                ];
            }
        }

        return $carriers;
    }

    /**
     * @return array
     */
    private function getCarriersInstances()
    {
        return $this->shippingConfig->getAllCarriers();
    }
}
