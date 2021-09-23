<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Source;

use Amasty\ImportCore\Api\Source\SourceConfigInterface;
use Magento\Framework\File\Mime;

class MimeValidator
{
    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var SourceConfigInterface
     */
    private $sourceConfig;

    /**
     * @var array
     */
    private $additionalMimeTypes = [];

    public function __construct(
        Mime $mime,
        SourceConfigInterface $sourceConfig
    ) {
        $this->mime = $mime;
        $this->sourceConfig = $sourceConfig;
    }

    /**
     * Returns true if the mime type of specified file matches a given source type.
     * Also validation by mime type parts available. For example: if 'mimeTypes' array of sourceConfig contains 'csv'
     * mime types will be accepted like "text/csv", "application/csv" and so on.
     *
     * @param string $sourceType
     * @param string $filePath
     * @return bool
     */
    public function isValid(string $sourceType, string $filePath): bool
    {
        $expectedMimeTypes = $this->getExpectedMimeTypes($sourceType);
        if (!$expectedMimeTypes) {
            return true;
        }

        $actualMimeType = $this->mime->getMimeType($filePath);

        if (in_array($actualMimeType, $expectedMimeTypes)) {
            return true;
        }

        $mimeParts = $this->getMimeParts($actualMimeType);
        foreach ($mimeParts as $mimePart) {
            if (in_array($mimePart, $expectedMimeTypes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $mimeType
     */
    public function addMimeType(string $mimeType): void
    {
        $this->additionalMimeTypes[] = $mimeType;
    }

    /**
     * Get expected mime types
     *
     * @param string $sourceType
     * @return array
     */
    private function getExpectedMimeTypes(string $sourceType): array
    {
        $sourceConfig = $this->sourceConfig->get($sourceType);
        if (isset($sourceConfig['mimeTypes'])) {
            $expectedTypes = is_array($sourceConfig['mimeTypes'])
                ? $sourceConfig['mimeTypes']
                : [$sourceConfig['mimeTypes']];
        } else {
            $expectedTypes = [$sourceType];
        }

        if (!empty($this->additionalMimeTypes)) {
            $expectedTypes = array_merge($expectedTypes, $this->additionalMimeTypes);
            $this->additionalMimeTypes = [];
        }

        return $expectedTypes;
    }

    /**
     * Get parts of mime type
     *
     * @param string $mimeType
     * @return array
     */
    private function getMimeParts(string $mimeType): array
    {
        $parts = explode('/', $mimeType);
        $parts = array_merge($parts, explode('-', $mimeType));
        $parts = array_merge($parts, explode(';', $mimeType));

        return $parts;
    }
}
