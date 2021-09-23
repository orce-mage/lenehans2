<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


namespace Amasty\Number\Api;

use Amasty\Number\Api\Data\CounterInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 */
interface CounterRepositoryInterface
{
    /**
     * Create an empty counter model
     *
     * @param array $data
     *
     * @return CounterInterface
     */
    public function create(array $data = []): CounterInterface;

    /**
     * Get counter model by ID
     *
     * @param $counterId
     *
     * @return CounterInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $counterId): CounterInterface;

    /**
     * Save counter model
     *
     * @param CounterInterface $counter
     *
     * @return CounterInterface
     * @throws CouldNotSaveException
     */
    public function save(CounterInterface $counter): CounterInterface;

    /**
     * Delete counter model
     *
     * @param CounterInterface $counter
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CounterInterface $counter): bool;
}
