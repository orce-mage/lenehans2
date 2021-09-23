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

namespace Magetrend\PdfTemplates\Model\Pdf;

/**
 * Abstract variable filter class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
abstract class Filter
{
    /**
     * @var \Magento\Sales\Model\AbstractModel
     */
    public $source;

    /**
     * @var \Magento\Sales\Model\Order
     */
    public $order = null;

    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    public $paymentHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    public $countryFactory;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    public $eventManager;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    public $dataObjectFactory;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    public $addressRenderer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    public $emulation;

    public $skipBillingFields = [
        'grand_total'
    ];

    /**
     * Returns entity data
     *
     * @return mixed
     */
    abstract public function getData();

    /**
     * Filter constructor.
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $emulation
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->paymentHelper = $paymentHelper;
        $this->objectManager = $objectManager;
        $this->countryFactory = $countryFactory;
        $this->eventManager = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->addressRenderer = $addressRenderer;
        $this->storeManager = $storeManager;
        $this->emulation = $emulation;
    }

    /**
     * Replace variables to data from source object
     *
     * @param $source
     * @param $string
     * @return mixed
     */
    public function processFilter($source, $string)
    {
        $this->source = $source;
        $this->order = null;
        $variables = $this->getData();
        if (empty($variables)) {
            return $string;
        }

        foreach ($variables as $key => $value) {
            if (!is_string($value) && !empty($value)) {
                continue;
            }

            if (is_array($value)) {
                continue;
            }

            $string = str_replace('{'.$key.'}', $value, $string);
        }
        return $string;
    }

    /**
     * Returns source object
     *
     * @return \Magento\Sales\Model\AbstractModel
     */
    public function getSource()
    {
        return $this->source;
    }

    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Returns order object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if ($this->order == null) {
            $source = $this->getSource();
            if ($source instanceof \Magento\Sales\Model\Order) {
                $this->order = $source;
            } else {
                $this->order = $source->getOrder();
            }
        }
        return $this->order;
    }

    /**
     * Returns grand total
     *
     * @return string
     */
    public function getGrandTotal()
    {
        return $this->getOrder()->formatPriceTxt($this->getSource()->getGrandTotal());
    }

    /**
     * Returns grand total
     *
     * @return string
     */
    public function getDue()
    {
        $order = $this->getOrder();
        return $this->getOrder()->formatPriceTxt($order->getGrandTotal() - $order->getTotalInvoiced());
    }

    /**
     * Returns billing data
     *
     * @param $data
     * @return mixed
     */
    public function addBillingData($data)
    {
        $data['fullname'] = '';
        $data['company'] = '';
        $data['address'] = '';
        $data['region'] = '';
        $data['vat_id'] = '';
        if ($this->getSource() instanceof \Magento\Quote\Model\Quote) {
            $source = $this->getSource();
        } else {
            $source = $this->getOrder();
        }

        $billingAddress = $source->getBillingAddress();
        $billingData = $billingAddress->getData();
        if (empty($billingData)) {
            return $data;
        }
        foreach ($billingData as $key => $value) {
            if (is_object($value) || in_array($key, $this->skipBillingFields)) {
                continue;
            }
            $data[$key] = $value;
        }

        $middleName = $billingAddress->getMiddlename();
        if (!empty($middleName)) {
            $middleName = ' '. $middleName;
        }

        $data['fullname'] = $billingAddress->getFirstname().$middleName.' '.$billingAddress->getLastname();

        if (isset($data['country_id']) && !empty($data['country_id'])) {
            $country = $this->countryFactory->create()->loadByCode($data['country_id']);
            $data['country'] = $country->getName();
        }

        $data['address'] = $this->getFormatedAddress($billingAddress);

        return $data;
    }

    /**
     * Returns billing data
     *
     * @param $data
     * @return mixed
     */
    public function addShippingData($data)
    {
        $data['s_fullname'] = '';
        $data['s_address'] = '';
        $data['s_region'] = '';
        $data['s_vat_id'] = '';
        $data['s_company'] = '';
        $source = $this->getSource();
        $shippingAddress = $source->getShippingAddress();
        if (!$shippingAddress) {
            return $data;
        }

        $shippingData = $shippingAddress->getData();
        if (empty($shippingData)) {
            return $data;
        }

        foreach ($shippingData as $key => $value) {
            if (is_object($value)) {
                continue;
            }
            $data['s_'.$key] = $value;
        }

        $middleName = $shippingAddress->getMiddlename();
        if (!empty($middleName)) {
            $middleName = ' '. $middleName;
        }
        $data['s_fullname'] = $shippingAddress->getFirstname().$middleName.' '.$shippingAddress->getLastname();

        if (isset($data['s_country_id']) && !empty($data['s_country_id'])) {
            $country = $this->countryFactory->create()->loadByCode($data['s_country_id']);
            $data['s_country'] = $country->getName();
        }

        $data['s_address'] = $this->getFormatedAddress($shippingAddress);

        return $data;
    }

    public function getFormatedAddress($address)
    {
        return $this->addressRenderer->format($address, 'pdf');
    }

    /**
     * Returns payment method information
     *
     * @param $data
     * @return mixed
     */
    public function addPaymentMethod($data)
    {
        $data['payment_method'] = '';
        $data['payment_additional'] = '';
        $data['payment_html'] = '';

        if ($this->getSource() instanceof \Magento\Quote\Model\Quote) {
            $source = $this->getSource();
        } else {
            $source = $this->getOrder();
        }

        try {
            $payment = $source->getPayment();
            $method = $payment->getMethodInstance();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $data;
        }

        $methodTitle = $method->getTitle();
        $data['payment_method'] = htmlspecialchars($methodTitle);
        $paymentConfig = $this->moduleHelper->getPaymentConfig($payment->getMethod());
        if (isset($paymentConfig['renderer'])) {
            $data['payment_additional']  = $this->objectManager->get($paymentConfig['renderer'])
                ->setData([
                    'payment' => $payment,
                    'payment_instance' => $method,
                    'order' => $source
                ])
                ->getValue();
        }

        $emulatedStoreId = $this->storeManager->getStore()->getId();
        $this->emulation->stopEnvironmentEmulation();

        $paymentHtml = $this->paymentHelper->getInfoBlockHtml(
            $source->getPayment(),
            $emulatedStoreId
        );

        $this->emulation->startEnvironmentEmulation(
            $emulatedStoreId,
            \Magento\Framework\App\Area::AREA_FRONTEND,
            true
        );

        $paymentHtml = str_replace(['<br>', '</br>', '<br/>', "\n"], '{br}', $paymentHtml);
        $paymentHtml = strip_tags($paymentHtml);
        $data['payment_html'] = $paymentHtml;

        return $data;
    }

    /**
     * Returns payment method information
     *
     * @param $data
     * @return mixed
     */
    public function addShippingMethod($data)
    {
        if ($this->getSource() instanceof \Magento\Quote\Model\Quote) {
            $shippingDescription = $this->getSource()->getShippingDescription();
        } else {
            $shippingDescription = $this->getOrder()->getShippingDescription();
        }

        $data['shipping_method'] = htmlspecialchars($shippingDescription);
        return $data;
    }

    /**
     * Add comments
     *
     * @param $data
     * @return mixed
     */
    public function addComments($data)
    {
        $data['comment_label'] = '';
        $data['comment_text'] = '';

        $source = $this->getSource();

        if ($source instanceof \Magento\Sales\Model\Order) {
            $commentsCollection = $source->getStatusHistoryCollection();
        } else {
            $commentsCollection = $source->getCommentsCollection();
        }

        if (!$commentsCollection || $commentsCollection->getSize() == 0) {
            return $data;
        }

        $comments = $commentsCollection->getItems();

        if (!empty($comments)) {
            $data['comment_label'] = (string)__('Notes:');
            foreach ($comments as $comment) {
                if ($comment->getData('is_visible_on_front') != 1) {
                    continue;
                }

                else {$commentText = ''; }
                $commentText = $comment->getComment();
                $commentText = str_replace(["\n", '<br/>', '</br>', '<br>', '</p>'], '{br}', $commentText);
                $commentText = strip_tags($commentText);
                $data['comment_text'].=$commentText."{br} {br}";
            }
        }
        return $data;
    }

    public function addTotals($data)
    {
        $pdfConfig = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Sales\Model\Order\Pdf\Config::class);
        $order = $this->getOrder();
        $source = $this->getSource();
        $totals = $pdfConfig->getTotals();

        $data['total_shipping_amount'] = $order->formatPriceTxt(0.00);
        foreach ($totals as $totalInfo) {
            $class = empty($totalInfo['model']) ? null : $totalInfo['model'];
            if (!$class) {
                continue;
            }
            $totalModel = \Magento\Framework\App\ObjectManager::getInstance()->create($class);
            $totalModel->setData($totalInfo);
            $totalModel->setOrder($order)->setSource($source);
            if ($totalModel->canDisplay()) {
                foreach ($totalModel->getTotalsForDisplay() as $totalData) {
                    $data['total_'.$totalInfo['source_field']] = $totalData['amount'];
                }
            }
        }

        return $data;
    }

    public function addAdditionalData($data, $type)
    {
        $dataObject = $this->dataObjectFactory
            ->create()
            ->setData($data);

        $this->eventManager->dispatch('magetrend_pdf_templates_add_additional_data', [
            'variable_list' => $dataObject,
            'source' => $this->getSource(),
            'order' => $this->getOrder()
        ]);
        $this->eventManager->dispatch('magetrend_pdf_templates_add_additional_data_'.$type, [
            'variable_list' => $dataObject,
            'source' => $this->getSource(),
            'order' => $this->getOrder()
        ]);
        $data = $dataObject->getData();

        return $data;
    }
}
