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

class CustomerGroup implements FieldValidatorInterface
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
            $customerGroupId = trim($row[$field]);

            if (!empty($customerGroupId)) {
                if (!isset($this->validationResult[$customerGroupId])) {
                    $this->validationResult[$customerGroupId] = $this->isCustomerGroupExists($customerGroupId);
                }

                return $this->validationResult[$customerGroupId];
            }
        }

        return true;
    }

    private function isCustomerGroupExists($customerId): bool
    {
        $customerGroupTable = $this->connection->getTableName('customer_group');
        $connection = $this->connection->getConnection();

        return is_numeric($connection->fetchOne(
            $connection->select()
                ->from($customerGroupTable)
                ->where('customer_group_id = ?', trim($customerId))
                ->limit(1)
                ->columns(['customer_group_id'])
        ));
    }
}
