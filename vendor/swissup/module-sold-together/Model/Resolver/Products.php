<?php
declare(strict_types=1);

namespace Swissup\SoldTogether\Model\Resolver;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;

use Swissup\SoldTogether\Model\Resolver\DataProvider\Products as DataProvider;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Products resolver, used for GraphQL request processing.
 * partial inspired Magento\CatalogGraphQl\Model\Resolver\Products
 */
class Products implements ResolverInterface
{

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param DataProvider $dataProvider
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DataProvider $dataProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->dataProvider = $dataProvider;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        if (!isset($args['productId']) ) {
            throw new GraphQlInputException(__('productId is required.'));
        }

        if (!isset($args['resourceType']) ) {
            throw new GraphQlInputException(__('resourceType is required.'));
        }
//        $searchResult = $this->searchQuery->getResult($args, $info);
//        if ($searchResult->getCurrentPage() > $searchResult->getTotalPages() && $searchResult->getTotalCount() > 0) {
//            throw new GraphQlInputException(
//                __(
//                    'currentPage value %1 specified is greater than the %2 page(s) available.',
//                    [$searchResult->getCurrentPage(), $searchResult->getTotalPages()]
//                )
//            );
//        }
        try {
            $provider = $this->dataProvider;
            if (isset($args['pageSize'])) {
                $provider->setPageSize((int) $args['pageSize']);
            }

            if (isset($args['currentPage'])) {
                $provider->setCurrentPage((int) $args['currentPage']);
            }
            $provider->setCurrentProductId($args['productId']);
            $provider->setResourceType($args['resourceType']);

            $storeId = $this->storeManager->getStore()->getId();
            $isShowOnlySimple = $args['resourceType'] === 'order' || !$this->isSetFlag($args['resourceType'], 'options', $storeId);
            $provider->setShowOnlySimple($isShowOnlySimple);
            $provider->setShowOutOfStock($this->isSetFlag($args['resourceType'], 'out', $storeId));
            $provider->setCanUseRandom($this->isSetFlag($args['resourceType'], 'random', $storeId));
            $provider->setLimit((int) $this->getConfigValue($args['resourceType'], 'count', $storeId));

            $data = $provider->getData();

//        } catch (NoSuchEntityException $e) {
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $data;
    }

    /**
     * Get config value
     *
     * @param  string $type
     * @param  string $key
     * @return string
     */
    private function getConfigValue(string $type, string $key, $scopeCode = null)
    {
        return (string) $this->scopeConfig->getValue(
            sprintf("soldtogether/%s/%s", $type, $key),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get config value
     *
     * @param  string $type
     * @param  string $key
     * @return bool
     */
    private function isSetFlag(string $type, string $key, $scopeCode = null)
    {
        return (bool) $this->scopeConfig->isSetFlag(
            sprintf("soldtogether/%s/%s", $type, $key),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }
}
