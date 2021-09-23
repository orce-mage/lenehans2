<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportExportCore
 */


declare(strict_types=1);

namespace Amasty\ImportExportCore\Config\SchemaReader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * @var string
     */
    private $schemaContent;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $rootDirectory;

    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    private $urnResolver;

    /**
     * @var \Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface
     */
    private $compiler;

    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\Config\ConverterInterface $converter,
        \Magento\Framework\Config\SchemaLocatorInterface $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        \Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface $compiler,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Config\Dom\UrnResolver $urnResolver,
        $fileName,
        $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
        $defaultScope = 'global'
    ) {
        $this->urnResolver = $urnResolver;
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->compiler = $compiler;
        $this->schemaContent = $this->prepareSchemaContent($schemaLocator->getSchema());

        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }

    protected function _readFiles($fileList)
    {
        /** @var \Magento\Framework\Config\Dom $configMerger */
        $configMerger = null;
        foreach ($fileList as $key => $content) {
            try {
                $content = $this->processDocument($content);

                if (!$configMerger) {
                    $configMerger = $this->_createConfigMerger($this->_domDocumentClass, $content);
                } else {
                    $configMerger->merge($content);
                }
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase("Invalid XML in file %1:\n%2", [$key, $e->getMessage()])
                );
            }
        }
        $this->validateMergedContent($configMerger);

        return $this->prepareOutput($configMerger);
    }

    public function validateMergedContent(\Magento\Framework\Config\Dom $configMerger): Reader
    {
        if ($this->validationState->isValidationRequired()) {
            $errors = [];
            if ($configMerger && !$configMerger->validate($this->_schemaFile, $errors)) {
                $message = "Invalid Document \n";
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase($message . implode("\n", $errors))
                );
            }
        }

        return $this;
    }

    public function prepareOutput(\Magento\Framework\Config\Dom $configMerger): array
    {
        $output = [];
        if ($configMerger) {
            $dom = $configMerger->getDom();
            if (!empty($this->schemaContent)) {
                $dom->schemaValidateSource($this->schemaContent, LIBXML_SCHEMA_CREATE);
            }
            $output = $this->_converter->convert($dom);
        }

        return $output;
    }

    public function processDocument(string $xml): string
    {
        $object = new DataObject();
        $document = new \DOMDocument();
        //prevent checking xml schema
        libxml_use_internal_errors(true);
        $document->loadXML($xml);
        $this->compiler->compile($document->documentElement, $object, $object);
        libxml_use_internal_errors(false);

        return $document->saveXML();
    }

    public function prepareSchemaContent(string $schemaPath): string
    {
        try {
            $schemaContent = $this->rootDirectory->readFile($schemaPath);
            $this->replaceUrn(
                '/[\"\'](urn:[a-zA-Z]*:module:[A-Za-z0-9\_]*:.+)[\"\']/i',
                $schemaContent
            );
            $this->replaceUrn(
                '/[\"\'](urn:[a-zA-Z]*:framework[A-Za-z\-]*:.+)[\"\']/',
                $schemaContent
            );

            return $schemaContent;
        } catch (LocalizedException $e) {
            return '';
        }
    }

    public function replaceUrn(string $pattern, string &$schemaContent): Reader
    {
        if (preg_match_all($pattern, $schemaContent, $matches)) {
            foreach ($matches[1] as $urn) {
                $schemaContent = str_replace($urn, $this->urnResolver->getRealPath($urn), $schemaContent);
            }
        }

        return $this;
    }
}
