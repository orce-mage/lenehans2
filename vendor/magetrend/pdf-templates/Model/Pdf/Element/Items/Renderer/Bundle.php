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

namespace Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer;

use Magetrend\PdfTemplates\Model\Config\Source\Adapter;

/**
 * Bundle item pdf renderer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Bundle extends \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer\DefaultRenderer
{
    public $bundleConfiguration;

    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magetrend\PdfTemplates\Model\Pdf\Decorator $decorator,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Helper\Image $image,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Sales\Model\Order\ItemFactory $quoteItemFactory,
        \Magento\Bundle\Helper\Catalog\Product\Configuration $bundleConfiguration
    ) {
        $this->bundleConfiguration = $bundleConfiguration;
        parent::__construct(
            $taxData,
            $decorator,
            $imageBuilder,
            $image,
            $assetRepo,
            $productRepository,
            $fileDriver,
            $filesystem,
            $imageFactory,
            $scopeConfig,
            $moduleHelper,
            $quoteItemFactory
        );
    }

    /**
     * Returns formated subtotal value
     *
     * @return string
     */
    public function getFormatedSubtotal()
    {
        $priceForDisplay = $this->getItemPricesForDisplay();

        $item =  $this->getItem();
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $qty = (int)$item->getQtyOrdered();
        } else {
            $qty = (int)$item->getQty();
        }

        $rowTotal = $priceForDisplay[0]['price'] * $qty;
        return $this->moduleHelper->formatPrice(
            $this->moduleHelper->getCurrencyCode($item->getStoreId()),
            $rowTotal
        );
    }

    public function getItemOptions()
    {
        $item = $this->getItem();
        if ($item instanceof \Magento\Quote\Model\Quote\Item) {
            return $this->getQuoteItemOptions($item);
        }

        return $this->getOrderItemOptions($item);
    }

    public function getOrderItemOptions($item)
    {
        $bundleOptions = [];
        $item = $this->getItem();
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }

        $currencyCode = $this->moduleHelper->getCurrencyCode($item->getStoreId());
        if ($options && isset($options['bundle_options'])) {
            foreach ($options['bundle_options'] as $option) {
                foreach ($option['value'] as $subOption) {
                    $price = $this->moduleHelper->formatPrice($currencyCode, $subOption['price']);
                    $bundleOptions[] = [
                        'label' => $subOption['title'],
                        'value' => $subOption['qty'].' x '.$price
                    ];
                }
            }
        }

        return $bundleOptions;
    }

    public function getQuoteItemOptions($item)
    {
        $options = $this->bundleConfiguration->getOptions($item);

        if (empty($options)) {
            return [];
        }
        $bundleOptions = [];
        foreach ($options as $option) {
            foreach ($option['value'] as $subOption) {
                $bundleOptions[] = [
                    'label' => $subOption
                ];
            }
        }

        return $bundleOptions;
    }

    public function getFormatedItemOptions()
    {
        $optionsString = parent::getFormatedItemOptions();
        if (!empty($optionsString)) {
            $optionsString = '{br}';
        }
        $options = $this->getItemOptions();
        $counter = count($options);
        foreach ($options as $key => $option) {
            $optionsString.= strip_tags($option['label']);

            if (isset($option['value'])) {
                $value = $option['value'];
                if ($this->moduleHelper->getAdapterName() == Adapter::ZEND_PDF) {
                    $value = $this->decorator->addDecorator(
                        $value,
                        \Magetrend\PdfTemplates\Model\Pdf\Decorator::TYPE_COLOR,
                        'table_row_product_line_2_value_color'
                    );
                }

                $optionsString.= ': ' .$value;
            }

            if ($counter - 1 != $key) {
                $optionsString.= '{br}';
            }
        }

        return $optionsString;
    }
}
