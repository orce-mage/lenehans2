<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Helper;

/**
 * Class Data
 * @package Wyomind\MassProductImport\Helper
 */
class Data extends \Wyomind\MassStockUpdate\Helper\Data
{
    /**
     * @var \Wyomind\MassProductImport\Model\ResourceModel\Rules\CollectionFactory
     */
    private $replacementCollectionFactory;

    /**
     * @var \Wyomind\MassProductImport\Model\RulesFactory
     */
    private $rulesFactory;

    /**
     * @var string
     */
    public $module = "MassProductImport";

    /**
     * @var \Magento\Framework\Api\FilterBuilder|null
     */
    private $filteruilder = null;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|null
     */
    private $searchCriteriaBuilder = null;

    /**
     * @var \Magento\Tax\Model\TaxClass\Repository
     */
    public $taxClassRepository;

    /**
     * @var string
     */
    const TMP_FOLDER = "/var/tmp/massproductimport/";
    /**
     * @var string
     */
    const UPLOAD_DIR = "/var/upload/";

    /**
     * @var string
     */
    const TMP_FILE_PREFIX = "massproductimport_";

    /**
     * @var string
     */
    const TMP_FILE_EXT = "orig";

    /**
     * @var int
     */
    const CSV = 1;

    /**
     * @var int
     */
    const XML = 2;
    /**
     * @var int
     */
    const JSON = 3;
    /**
     * @var int
     */
    const UPDATE = 1;

    /**
     * @var int
     */
    const IMPORT = 2;

    /**
     * @var int
     */
    const UPDATEIMPORT = 3;

    /**
     * @var array
     */
    const MODULES = [
        10 => "System",
        20 => "Price",
        25 => "TierPrice",
        30 => "AdvancedInventory",
        40 => "Stock",
        45 => "Msi",
        50 => "Attribute",
        60 => "CustomOption",
        70 => "Image",
        80 => "Category",
        90 => "Merchandising",
        100 => "DownloadableProduct",
        110 => "ConfigurableProduct",
        120 => "MixedProduct",
        140 => 'BundleProduct',
        11 => "ConfigurableProductsSystem",
        21 => "ConfigurableProductsPrice",
        41 => "ConfigurableProductsStock",
        51 => "ConfigurableProductsAttribute",
        71 => "ConfigurableProductsImage",
        81 => "ConfigurableProductsCategory",
//        91 => "ConfigurableProductsMerchandising",
//        999 => "CustomScript",
        1000 => "Ignored",

    ];

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory
     * @param \Wyomind\Framework\Helper\Module $framework
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\ObjectManager\ObjectManager $objectManager
     * @param \Magento\Catalog\Model\ProductFactory $productModelFactory
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Tax\Model\TaxClass\Repository $taxClassRepository
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Replacement\CollectionFactory $replacementCollectionFactory
     * @param \Wyomind\MassProductImport\Model\RulesFactory $rulesFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory,
        \Wyomind\Framework\Helper\Module $framework,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Magento\Framework\ObjectManager\ObjectManager $objectManager,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Tax\Model\TaxClass\Repository $taxClassRepository,
        \Wyomind\MassProductImport\Model\ResourceModel\Replacement\CollectionFactory $replacementCollectionFactory,
        \Wyomind\MassProductImport\Model\RulesFactory $rulesFactory
    ) {
    
        $this->filteruilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->taxClassRepository = $taxClassRepository;
        $this->replacementCollectionFactory = $replacementCollectionFactory;
        $this->rulesFactory = $rulesFactory;
        parent::__construct($context, $driverFileFactory, $framework, $storeManager, $attributeRepository, $productModelFactory, $objectManager);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    function getTaxClasses()
    {
        $this->taxClasses = [];
        $this->searchCriteriaBuilder->addFilter("class_type", "PRODUCT", "eq");
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $taxClasses = $this->taxClassRepository->getList($searchCriteria);
        foreach ($taxClasses->getItems() as $taxClass) {
            $this->taxClasses[$taxClass->getClassId()] = ($taxClass->getClassName());
        }
        $this->taxClasses[0] = "None";
        return $this->taxClasses;
    }

    /**
     * @return array
     */
    public function getProductTypeIds()
    {
        return [
            "simple" => (string)__("Simple"),
            "configurable" => (string)__("Configurable"),
            "grouped" => (string)__("Grouped"),
            "bundle" => (string)__("Bundle"),
            "donwloadable" => (string)__("Downloadable"),
            "virtual" => (string)__("Virtual"),
            "giftcard" => (string)__("Gift Card")];

    }

    /**
     * @return array
     */
    public function getVisibility()
    {
        return [
            1 => "Not Visible Individually",
            2 => "Catalog",
            3 => "Search",
            4 => "Catalog, Search"
        ];
    }

    /**
     * @param $ruleId
     * @param $value
     * @return mixed|null|string|string[]
     */
    public function replacementRules($ruleId, $value)
    {
        $replacements = $this->replacementCollectionFactory->create()->getCollectionByRuleId($ruleId);
        if (count($replacements)) {
            $rule = $this->rulesFactory->create();
            $regExp = $rule->load($ruleId)->getUseRegexp();
            if (!$regExp) {
                $out = $value;
                $in = $value;
                foreach ($replacements as $replacement) {
                    $out = str_replace($replacement->getInput(), $replacement->getOutput(), $out);
                }
                if ($in != $out) {
                    return $out;
                }
            } else {
                foreach ($replacements as $replacement) {
                    $in = $value;
                    $out = preg_replace("#^" . $replacement->getInput() . "$#m", $replacement->getOutput(), $value, 1);
                    if ($in != $out) {
                        return $out;
                    }
                }
            }
        }

        return $value;
    }
}
