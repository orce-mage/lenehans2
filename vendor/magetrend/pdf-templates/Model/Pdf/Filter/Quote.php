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

namespace Magetrend\PdfTemplates\Model\Pdf\Filter;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Invoice varialble filter class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Quote extends \Magetrend\PdfTemplates\Model\Pdf\Filter
{
    /**
     * Returns invoice data
     *
     * @return array
     */
    public function getData()
    {
        $quote = $this->getSource();
        $currencyCode = $this->moduleHelper->getCurrencyCode($quote->getStoreId());
        $data = [
            'quote_id' => $quote->getId(),
            'quote_no' => $quote->getId(),
            'quote_date' => $this->moduleHelper->formatDate($quote->getCreatedAt(), $quote->getStoreId()),
            'grand_total' => $this->moduleHelper->formatPrice($currencyCode, $quote->getGrandTotal()),
        ];

        if ($customerNote = $quote->getCustomerNote()) {
            $data['customer_note'] = $customerNote;
        }

        $data = $this->addBillingData($data);
        $data = $this->addShippingData($data);
        $data = $this->addPaymentMethod($data);
        $data = $this->addShippingMethod($data);
        $data = $this->addTotals($data);
        $data = $this->addAdditionalData($data, 'quote');

        return $data;
    }

    public function getFormatedAddress($address)
    {
        return '';
    }

    public function addTotals($data)
    {
        $pdfConfig = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Sales\Model\Order\Pdf\Config::class);
        $source = $this->getSource();
        $totals = $source->getTotals();
        
        if (empty($totals)) {
            return $data;
        }

        $currency = $this->moduleHelper->getCurrencyCode($source->getStoreId());
        $data['total_shipping_amount'] = $this->moduleHelper->formatPrice($currency, 0.00);
        foreach ($totals as $total) {
            $total->setQuote($source)->setSource($source);
            $data['total_'.$total->getCode()] = $this->moduleHelper->formatPrice($currency, $total->getAmount());
        }

        return $data;
    }
}
