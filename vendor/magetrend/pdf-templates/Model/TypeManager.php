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

namespace Magetrend\PdfTemplates\Model;

/**
 * Template manager class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class TypeManager
{
    /**
     * @var TemplateFactory
     */
    public $adapter = null;

    public $templateType = null;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    public $order;

    public $invoice;

    public $shipment;

    public $creditMemo;

    public $quote;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magetrend\PdfTemplates\Model\Type\Order $order,
        \Magetrend\PdfTemplates\Model\Type\Invoice $invoice,
        \Magetrend\PdfTemplates\Model\Type\Shipment $shipment,
        \Magetrend\PdfTemplates\Model\Type\CreditMemo $creditMemo,
        \Magetrend\PdfTemplates\Model\Type\Quote $quote
    ) {
        $this->registry = $registry;
        $this->order = $order;
        $this->invoice = $invoice;
        $this->shipment = $shipment;
        $this->quote = $quote;
        $this->creditMemo = $creditMemo;
    }

    /**
     * @return \Magetrend\PdfTemplates\Model\TypeAbstract|null
     */
    public function getAdapter($sourceObject = null)
    {
        if ($this->adapter !== null) {
            return $this->adapter;
        }

        if ($sourceObject === null) {
            switch ($this->getTemplateType()) {
                case \Magetrend\PdfTemplates\Model\Type\Order::TYPE:
                    $this->adapter = $this->order;
                    break;
                case \Magetrend\PdfTemplates\Model\Type\Invoice::TYPE:
                    $this->adapter = $this->invoice;
                    break;
                case \Magetrend\PdfTemplates\Model\Type\Shipment::TYPE:
                    $this->adapter = $this->shipment;
                    break;
                case \Magetrend\PdfTemplates\Model\Type\CreditMemo::TYPE:
                    $this->adapter = $this->creditMemo;
                    break;
                case \Magetrend\PdfTemplates\Model\Type\Quote::TYPE:
                    $this->adapter = $this->quote;
                    break;
            }
        }

        if ($sourceObject !== null) {
            if ($sourceObject instanceof \Magento\Sales\Model\Order) {
                $this->adapter = $this->order;
            } elseif ($sourceObject instanceof \Magento\Sales\Model\Order\Invoice) {
                $this->adapter = $this->invoice;
            } elseif ($sourceObject instanceof \Magento\Sales\Model\Order\Shipment) {
                $this->adapter = $this->shipment;
            } elseif ($sourceObject instanceof \Magento\Sales\Model\Order\Creditmemo) {
                $this->adapter = $this->creditMemo;
            } elseif ($sourceObject instanceof \Magento\Quote\Model\Quote) {
                $this->adapter = $this->quote;
            }
        }

        return $this->adapter;
    }

    public function getTemplateType()
    {
        if ($this->templateType != null) {
            return $this->templateType;
        }

        $template = $this->registry->registry('current_pdf_template');
        if (!$template || !$template->getId()) {
            return false;
        }

        return $template->getType();
    }

    public function setTemplateType($type)
    {
        $this->templateType = $type;
        return $this;
    }

    public function resetManager()
    {
        $this->templateType = null;
        $this->adapter = null;
    }
}
