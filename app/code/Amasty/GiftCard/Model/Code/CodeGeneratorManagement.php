<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Code;

use Amasty\GiftCard\Api\CodeGeneratorManagementInterface;

class CodeGeneratorManagement implements CodeGeneratorManagementInterface
{
    /**
     * @var \Amasty\GiftCard\Model\CodePool\Repository
     */
    private $codePoolRepository;

    /**
     * @var CodeGeneratorFactory
     */
    private $codeGeneratorFactory;

    public function __construct(
        \Amasty\GiftCard\Model\CodePool\Repository $codePoolRepository,
        \Amasty\GiftCard\Model\Code\CodeGeneratorFactory $codeGeneratorFactory
    ) {
        $this->codePoolRepository = $codePoolRepository;
        $this->codeGeneratorFactory = $codeGeneratorFactory;
    }

    public function generateCodesForCodePool(int $codePoolId, int $qty): bool
    {
        $codePool = $this->codePoolRepository->getById($codePoolId);

        return $this->generateCodesByTemplate($codePoolId, $codePool->getTemplate(), $qty);
    }

    public function generateCodesByTemplate(int $codePoolId, string $template, int $qty): bool
    {
        /** @var \Amasty\GiftCard\Model\Code\CodeGenerator $codeGenerator */
        $codeGenerator = $this->codeGeneratorFactory->create([
            'codePoolId' => $codePoolId
        ]);
        $codeGenerator->generateCodes($template, $qty);

        return true;
    }

    public function generateCodesByFile(int $codePoolId, array $file): bool
    {
        /** @var \Amasty\GiftCard\Model\Code\CodeGenerator $codeGenerator */
        $codeGenerator = $this->codeGeneratorFactory->create([
            'codePoolId' => $codePoolId
        ]);
        $codeGenerator->generateCodesFromCsv($file);

        return true;
    }
}
