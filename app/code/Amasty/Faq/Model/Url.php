<?php
declare(strict_types=1);

namespace Amasty\Faq\Model;

use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Api\Data\QuestionInterface;
use Magento\Framework\UrlInterface;

/**
 * FAQ Url Model
 */
class Url
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ConfigProvider $configProvider,
        \Magento\Framework\Url $urlBuilder
    ) {
        $this->configProvider = $configProvider;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get FAQ base url
     *
     * @return string
     */
    public function getFaqUrl(): string
    {
        return $this->urlBuilder->getUrl($this->configProvider->getUrlKey());
    }

    /**
     * Get category path
     *
     * @param CategoryInterface $category
     * @return string
     */
    public function getCategoryPath(CategoryInterface $category): string
    {
        $pathParts = $this->getCategoryPathParts($category);

        return implode('/', $pathParts);
    }

    /**
     * Get category url
     *
     * @param CategoryInterface $category
     * @param array $params
     * @return string
     */
    public function getCategoryUrl(CategoryInterface $category, array $params = []): string
    {
        $pathParts = $this->getCategoryPathParts($category);

        return $this->getEntityUrl($pathParts, $params);
    }

    /**
     * Get category path parts
     *
     * @param CategoryInterface $category
     * @return array
     */
    private function getCategoryPathParts(CategoryInterface $category): array
    {
        return [
            $this->configProvider->getUrlKey(),
            $category->getUrlKey()
        ];
    }

    /**
     * Get canonical category url
     *
     * @param CategoryInterface $category
     * @return string
     */
    public function getCanonicalCategoryUrl(CategoryInterface $category): string
    {
        $pathParts = [$this->configProvider->getUrlKey(), $category->getCanonicalUrl()];

        return $this->getEntityUrl($pathParts);
    }

    /**
     * Get question url
     *
     * @param QuestionInterface $question
     * @param bool $useCanonical
     * @return string
     */
    public function getQuestionUrl(QuestionInterface $question, $useCanonical = false): string
    {
        $pathParts = [$this->configProvider->getUrlKey()];

        $canonicalUrlKey = $question->getCanonicalUrl();
        if ($useCanonical
            && $canonicalUrlKey
            && $this->configProvider->isCanonicalUrlEnabled()
        ) {
            $pathParts[] = $canonicalUrlKey;
        } else {
            $pathParts[] = $question->getUrlKey();
        }

        return $this->getEntityUrl($pathParts);
    }

    /**
     * Get canonical question url
     *
     * @param QuestionInterface $question
     * @return string
     */
    public function getCanonicalQuestionUrl(QuestionInterface $question): string
    {
        $pathParts = [$this->configProvider->getUrlKey(), $question->getCanonicalUrl()];

        return $this->getEntityUrl($pathParts);
    }

    /**
     * Get entity url using url path parts
     *
     * @param array $pathParts
     * @param array $params
     * @return string
     */
    public function getEntityUrl(array $pathParts, array $params = []): string
    {
        $routeParams = [];
        if (!empty($params)) {
            $routeParams['_query'] = $params;
        }

        $path = $this->composeEntityPath($pathParts);
        if ($this->configProvider->isAddUrlSuffix() || $this->configProvider->isRemoveTrailingSlash()) {
            $routeParams['_direct'] = $path;

            return $this->urlBuilder->getUrl(null, $routeParams);
        }

        return $this->urlBuilder->getUrl($path, $routeParams);
    }

    /**
     * Get entity path info
     *
     * @param array $pathParts
     * @return string
     */
    public function getEntityPathInfo(array $pathParts): string
    {
        $path = $this->composeEntityPath($pathParts);
        // This introduced for compatibility with url generation with trailing slash
        if (!$this->configProvider->isAddUrlSuffix()
            && !$this->configProvider->isRemoveTrailingSlash()
        ) {
            $path .= '/';
        }

        return $path;
    }

    /**
     * Compose entity path from path parts
     *
     * @param array $pathParts
     * @return string
     */
    private function composeEntityPath(array $pathParts): string
    {
        $path = implode('/', $pathParts);
        if ($this->configProvider->isAddUrlSuffix()) {
            $path .= $this->configProvider->getUrlSuffix();
        }

        return $path;
    }
}
