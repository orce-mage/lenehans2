<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCard\Model\Code\Repository as CodeRepository;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

class GiftCardAccountsGenerator
{
    /**
     * @var CodeRepository
     */
    private $codeRepository;

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var Date
     */
    private $dateFilter;

    public function __construct(
        CodeRepository $codeRepository,
        Repository $accountRepository,
        Date $dateFilter
    ) {
        $this->codeRepository = $codeRepository;
        $this->accountRepository = $accountRepository;
        $this->dateFilter = $dateFilter;
    }

    /**
     * @param DataObject $accountsFormData
     * @param int $qty
     * @return void
     *
     * @throws LocalizedException
     * @throws \Exception
     */
    public function generate(DataObject $accountsFormData, int $qty): void
    {
        $codePoolId = (int)$accountsFormData->getCodePool();

        if (!$this->validateCodePoolCodesQty($codePoolId, $qty)) {
            throw new LocalizedException(__(
                'Unable to generate new accounts. Error: No available codes found for Code Pool with ID "%1".',
                $codePoolId
            ));
        }

        $accountsData = $this->prepareAccountsData($accountsFormData);
        for ($i = 0; $i < $qty; $i++) {
            $account = $this->accountRepository->getEmptyAccountModel();
            $account->setData($accountsData);

            $this->accountRepository->save($account);
        }
    }

    /**
     * @param int $codePoolId
     * @param int $qty
     * @return bool
     */
    private function validateCodePoolCodesQty(int $codePoolId, int $qty): bool
    {
        $availableCodes = $this->codeRepository->getAvailableCodesByCodePoolId($codePoolId);

        return $qty <= count($availableCodes);
    }

    /**
     * @param DataObject $accountsFormData
     * @return array
     *
     * @throws \Exception
     */
    private function prepareAccountsData(DataObject $accountsFormData): array
    {
        $accountsData = $accountsFormData->getData();

        if ($balance = $accountsData['balance'] ?? 0) {
            $accountsData[GiftCardAccountInterface::INITIAL_VALUE] =
            $accountsData[GiftCardAccountInterface::CURRENT_VALUE] = $balance;
        }

        if ($expiredDate = $accountsData[GiftCardAccountInterface::EXPIRED_DATE] ?? '') {
            $accountsData[GiftCardAccountInterface::EXPIRED_DATE] = $this->dateFilter->filter($expiredDate);
        }
        unset($accountsData['qty']);

        return $accountsData;
    }
}
