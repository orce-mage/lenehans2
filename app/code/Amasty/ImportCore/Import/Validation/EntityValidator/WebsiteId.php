<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Validation\EntityValidator;

use Amasty\ImportCore\Api\Validation\FieldValidatorInterface;
use Magento\Framework\App\ResourceConnection;

class WebsiteId implements FieldValidatorInterface
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var array
     */
    private $validationResult = [];

    public function __construct(ResourceConnection $connection)
    {
        $this->connection = $connection;
    }

    public function validate(array $row, string $field): bool
    {
        if (isset($row[$field])) {
            $websiteId = trim($row[$field]);

            if (!empty($websiteId)) {
                if (!isset($this->validationResult[$websiteId])) {
                    $this->validationResult[$websiteId] = $this->isWebsiteExists($websiteId);
                }

                return $this->validationResult[$websiteId];
            }
        }

        return true;
    }

    private function isWebsiteExists($websiteId): bool
    {
        $storeTable = $this->connection->getTableName('store_website');
        $connection = $this->connection->getConnection();

        return (bool)$connection->fetchOne(
            $connection->select()
                ->from($storeTable)
                ->where('website_id = ?', trim($websiteId))
                ->limit(1)
                ->columns(['website_id'])
        );
    }
}
