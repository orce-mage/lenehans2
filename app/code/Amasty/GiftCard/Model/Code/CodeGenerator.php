<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Code;

use Amasty\GiftCard\Model\OptionSource\Status;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;

class CodeGenerator
{
    const MAX_QTY = 10000;
    const MAX_FILE_SIZE = 2500000;
    const ALLOWED_FILE_EXTENSIONS = ['csv'];
    const MASK = [
        // no "0" and "1" as they are confusing
        '{D}' => [2, 3, 4, 5, 6, 7, 8, 9],
        // no I, Q and O as they are confusing
        '{L}' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M',
                  'N', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z']
    ];

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var int|null
     */
    private $codePoolId;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    protected $generatedCodes = [];

    /**
     * @var array
     */
    protected $existingCodes = [];

    /**
     * @var array
     */
    private $templateMasksList = [];

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var File
     */
    private $ioFile;

    public function __construct(
        Repository $repository,
        Filesystem $filesystem,
        Escaper $escaper,
        File $ioFile,
        $codePoolId = null
    ) {
        $this->repository = $repository;
        $this->codePoolId = $codePoolId;
        $this->filesystem = $filesystem;
        $this->escaper = $escaper;
        $this->ioFile = $ioFile;
    }

    /**
     * @param string $template
     * @param int $qty
     *
     * @throws LocalizedException
     */
    public function generateCodes(string $template, int $qty)
    {
        $template = $this->escaper->escapeHtml($template);
        $this->initializeByTemplate($template);
        $this->validateTemplate($template, $qty);

        for ($i = 0; $i < $qty; $i++) {
            $code = $this->generateCode($template);
            $this->generatedCodes[] = $code;
        }

        $this->saveGeneratedCodes();
        $this->clear();
    }

    /**
     * @param array $file
     *
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function generateCodesFromCsv(array $file)
    {
        $this->validateFile($file);
        $this->initializeByFile();
        $directoryReader = $this->filesystem->getDirectoryRead(DirectoryList::SYS_TMP);

        if (!$directoryReader->isExist($file['tmp_name'])) {
            return;
        }
        $stream = $directoryReader->openFile($file['tmp_name']);

        while ($csvLine = $stream->readCsv()) {
            if (!$csvLine) {
                continue;
            }
            $code = array_shift($csvLine);

            if (!$this->isCodeExist($code)) {
                $this->generatedCodes[] = $code;
            }
        }
        $this->saveGeneratedCodes();
        $this->clear();
    }

    /**
     * @param string $template
     *
     * @throws LocalizedException
     */
    private function initializeByTemplate(string $template)
    {
        if (!$this->codePoolId) {
            throw new LocalizedException(__('Please specify Code Pool ID before codes generation.'));
        }
        $dbTemplate = $template;

        foreach (self::MASK as $placeholder => $values) {
            $dbTemplate = str_replace($placeholder, "_", $dbTemplate);
        }
        $this->existingCodes = $this->repository->getCodesByTemplate($dbTemplate);

        $masks = array_map(
            function ($value) {
                return preg_quote($value, '/');
            },
            array_keys(self::MASK)
        );
        $regExpTemplate = implode('|', $masks);

        if (preg_match_all('/' . $regExpTemplate . '/', $template, $matches)) {
            $this->templateMasksList = $matches[0];
        }
    }

    /**
     * @throws LocalizedException
     */
    private function initializeByFile()
    {
        if (!$this->codePoolId) {
            throw new LocalizedException(__('Please specify Code Pool ID before codes generation.'));
        }
        $dbExistingCodes = $this->repository->getAllCodes();

        if ($this->existingCodes) {
            $this->existingCodes = array_merge($this->existingCodes, $dbExistingCodes);
        } else {
            $this->existingCodes = $dbExistingCodes;
        }
    }

    /**
     * @param string $template
     *
     * @return string
     */
    private function generateCode(string $template): string
    {
        $code = $template;

        foreach ($this->templateMasksList as $templateSymbol) {
            $possibleValues = self::MASK[$templateSymbol];
            $symbol = $possibleValues[array_rand($possibleValues)];
            $code = preg_replace('/' . preg_quote($templateSymbol, '/') . '/', $symbol, $code, 1);
        }

        if ($this->isCodeExist($code)) {
            return $this->generateCode($template);
        }

        return $code;
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    private function isCodeExist(string $code): bool
    {
        return in_array($code, $this->existingCodes) || in_array($code, $this->generatedCodes);
    }

    /**
     * @param string $template
     * @param int $qty
     *
     * @throws LocalizedException
     */
    private function validateTemplate(string $template, int $qty)
    {
        if (false === strpos($template, '{L}') && false === strpos($template, '{D}')) {
            throw new LocalizedException(__('Please add {L} or {D} placeholders into the template "%1"', $template));
        }

        if ($qty > self::MAX_QTY) {
            throw new LocalizedException(__('At a time, you can generate no more than %1 codes.', self::MAX_QTY));
        }
        $templateCodesQty = $this->getTemplateAvailableCodesQty($template);

        if ($qty > $templateCodesQty) {
            throw new LocalizedException(__('Maximum number of code combinations for the current template is %1, 
            please update Quantity field accordingly.', $templateCodesQty));
        }
    }

    /**
     * @param array $file
     *
     * @throws LocalizedException
     */
    private function validateFile(array $file)
    {
        if (!in_array($this->ioFile->getPathInfo($file['name'])['extension'], self::ALLOWED_FILE_EXTENSIONS)) {
            throw new LocalizedException(__('Wrong file extension. Please use only .csv files.'));
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new LocalizedException(__('The file size is too big.'));
        }
    }

    /**
     * @param string $template
     *
     * @return int
     */
    private function getTemplateAvailableCodesQty(string $template): float
    {
        return (float)($this->getTemplateMaxCodesQty($template) - $this->getTemplateExistingCodesQty($template));
    }

    /**
     * @param string $template
     *
     * @return int
     */
    private function getTemplateMaxCodesQty(string $template): float
    {
        $maxQty = 1;

        foreach (self::MASK as $placeholder => $values) {
            $allValuesCount = count($values);
            $templateValuesCount = substr_count($template, $placeholder);
            $maxQty *= pow($allValuesCount, $templateValuesCount);
        }

        return (float)$maxQty;
    }

    /**
     * @param string $template
     *
     * @return int
     */
    private function getTemplateExistingCodesQty(string $template): int
    {
        foreach (self::MASK as $placeholder => $values) {
            $template = str_replace($placeholder, "_", $template);
        }

        return $this->repository->getCodesCountByTemplate($template);
    }

    /**
     * @throws CouldNotSaveException
     */
    private function saveGeneratedCodes()
    {
        foreach ($this->generatedCodes as $code) {
            $model = $this->repository->getEmptyCodeModel();
            $model->setCodePoolId($this->codePoolId)
                ->setCode($code)
                ->setStatus(Status::AVAILABLE);
            $this->repository->save($model);
        }
    }

    /**
     * Clears generated codes storage
     */
    private function clear()
    {
        $this->existingCodes = array_merge($this->generatedCodes, $this->existingCodes);
        $this->generatedCodes = [];
    }
}
