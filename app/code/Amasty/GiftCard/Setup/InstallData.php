<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Setup;

use Amasty\GiftCard\Api\CodePoolRepositoryInterface;
use Amasty\GiftCard\Model\Code\CodeGeneratorManagement;
use Amasty\GiftCard\Setup\Operation\InstallImageData;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Math\Random;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    const SAMPLE_CODES_QTY = 1000;

    /**
     * @var Operation\AddGiftCardAttributes
     */
    private $addGiftCardAttributes;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var CodePoolRepositoryInterface
     */
    private $codePoolRepository;

    /**
     * @var CodeGeneratorManagement
     */
    private $codeGeneratorManagement;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var InstallImageData
     */
    private $installImageData;

    public function __construct(
        Operation\AddGiftCardAttributes $addGiftCardAttributes,
        State $appState,
        CodePoolRepositoryInterface $codePoolRepository,
        CodeGeneratorManagement $codeGeneratorManagement,
        Random $random,
        InstallImageData $installImageData
    ) {
        $this->addGiftCardAttributes = $addGiftCardAttributes;
        $this->appState = $appState;
        $this->codePoolRepository = $codePoolRepository;
        $this->codeGeneratorManagement = $codeGeneratorManagement;
        $this->random = $random;
        $this->installImageData = $installImageData;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->addGiftCardAttributes->execute($setup);
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'installModuleData']);
    }

    public function installModuleData()
    {
        $this->installImageData->addImageTemplates();
        $this->generateDefaultCodePool();
    }

    protected function generateDefaultCodePool()
    {
        $randomTemplate = $this->random->getRandomString(3, "ABCDEFGHJKMNPRSTUVWXYZ")
            . '_' . $this->random->getRandomString(3, '23456789') . '_{L}{L}{D}{D}';

        $model = $this->codePoolRepository->getEmptyCodePoolModel();
        $model->setTitle('Sample Code Set')
            ->setTemplate($randomTemplate);

        try {
            $this->codePoolRepository->save($model);

            if (!$model->getCodePoolId()) {
                return;
            }
            $this->codeGeneratorManagement->generateCodesForCodePool($model->getCodePoolId(), self::SAMPLE_CODES_QTY);
        } catch (\Exception $e) {
            return;
        }
    }
}
