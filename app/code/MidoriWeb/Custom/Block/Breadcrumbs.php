<?php
namespace MidoriWeb\Custom\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data;

class Breadcrumbs extends \Magento\Catalog\Block\Product\View
{

    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogData = null;


    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        Data $catalogData,
        array $data = []
    ) {
        $this->_catalogData = $catalogData;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    public function getBreadcrumbs()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'label' => 'Home',
            'title' => 'Go to Home Page',
            'link' => $this->_storeManager->getStore()->getBaseUrl()
        );

        $path = $this->_catalogData->getBreadcrumbPath();

        if(count($path)<2)
        {
            $product = $this->getProduct();
            $categoryCollection = clone $product->getCategoryCollection();
            $categoryCollection->clear();
            $categoryCollection->addAttributeToSort('level', $categoryCollection::SORT_ORDER_DESC)
                ->addAttributeToFilter('path', array('like' => "1/" . $this->_storeManager->getStore()->getRootCategoryId() . "/%"));
            $categoryCollection->setPageSize(1);
            $breadcrumbCategories = $categoryCollection->getFirstItem()->getParentCategories();
            $url_count = count(explode('/',$this->_storeManager->getStore()->getBaseUrl()));
            foreach ($breadcrumbCategories as $category) {
                $url_keys = explode('/',$category->getUrl());

                $url_count = count($url_keys);
                $breadcrumbs[$url_count] = array(
                    'label' => $category->getName(),
                    'title' => $category->getName(),
                    'link' => $category->getUrl(),
                    'count' => $url_count
                );
            }

            ksort($breadcrumbs);

            $breadcrumbs[] = array(
                'label' => $product->getName(),
                'title' => $product->getName(),
                'link' => ''
            );
        }
        else
        {
            foreach ($path as $name => $breadcrumb) {
                $breadcrumbs[] = array(
                    'label' => $breadcrumb['label'],
                    'title' => $breadcrumb['label'],
                    'link' => @$breadcrumb['link']
                );
            }
        }

        return $breadcrumbs;
    }
}
