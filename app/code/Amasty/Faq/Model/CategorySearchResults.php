<?php
declare(strict_types=1);

namespace Amasty\Faq\Model;

use Amasty\Faq\Api\Data\CategorySearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Categories search results.
 */
class CategorySearchResults extends SearchResults implements CategorySearchResultsInterface
{
}
