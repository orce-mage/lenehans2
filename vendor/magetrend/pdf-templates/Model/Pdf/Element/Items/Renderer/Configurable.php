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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Config\Source\Product\Thumbnail;

/**
 * Bundle item pdf renderer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Configurable extends \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer\DefaultRenderer
{

    public $catalogProductTypeConfigurable;

    public $configuration;

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
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Catalog\Helper\Product\Configuration $configuration
    ) {
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->configuration = $configuration;
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

    public function getQuoteItemOptions($item)
    {
        $options = $this->configuration->getOptions($item);
        return ['options' => $options];
    }

    public function getProductIdImage()
    {
        $item = $this->getItem();
        if ($this->isUseChildProduct()) {
            return $item->getProductId();
        }

        try {
            $childProduct = $this->productRepository->getById($item->getProductId());
        } catch (NoSuchEntityException $e) {
            return $item->getProductId();
        }

        $parentByChild = $this->catalogProductTypeConfigurable->getParentIdsByChild($childProduct->getId());
        if (!isset($parentByChild[0])) {
            return $item->getProductId();
        }

        $parentProductId = $parentByChild[0];
        try {
            $parentProduct = $this->productRepository->getById($parentProductId);
        } catch (NoSuchEntityException $e) {
            return $item->getProductId();
        }

        return $parentProduct->getId();
    }

    public function isUseChildProduct()
    {
        $storeId = $this->getSource()->getStoreId();

        $configValue = $this->scopeConfig->getValue(
            \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable::CONFIG_THUMBNAIL_SOURCE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        try {
            $childProduct = $this->productRepository->getById($this->getItem()->getProductId(), false, $storeId);
        } catch (NoSuchEntityException $e) {
            return false;
        }

        $childThumb = $childProduct->getData('thumbnail');
        return $configValue !== Thumbnail::OPTION_USE_PARENT_IMAGE
            && $childThumb !== null
            && $childThumb !== 'no_selection';
    }
}
