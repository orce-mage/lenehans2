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

class CustomerId implements FieldValidatorInterface
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
            $customerId = trim($row[$field]);

            if (!empty($customerId)) {
                if (!isset($this->validationResult[$customerId])) {
                    $this->validationResult[$customerId] = $this->isCustomerExists($customerId);
                }

                return $this->validationResult[$customerId];
            }
        }

        return true;
    }

    private function isCustomerExists($customerId): bool
    {
        $customerTable = $this->connection->getTableName('customer_entity');
        $connection = $this->connection->getConnection();

        return (bool)$connection->fetchOne(
            $connection->select()
                ->from($customerTable)
                ->where('entity_id = ?', $customerId)
                ->limit(1)
                ->columns(['entity_id'])
        );
    }
}
