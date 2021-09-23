<?php
/**
 * Default configuration data reader. Reads configuration data from storage
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\MbFrame\Framework\App\Config\Initial;

use Magento\Framework\Filesystem\Directory\ReadFactory;

class Reader extends \Magento\Framework\App\Config\Initial\Reader
{
    /**
     * File locator
     *
     * @var \Magento\Framework\Config\FileResolverInterface
     */
    protected $_fileResolver;

    /**
     * Config converter
     *
     * @var  \Magento\Framework\Config\ConverterInterface
     */
    protected $_converter;

    /**
     * Config file name
     *
     * @var string
     */
    protected $_fileName;

    /**
     * Class of dom configuration document used for merge
     *
     * @var string
     */
    protected $_domDocumentClass;

    /**
     * Scope priority loading scheme
     *
     * @var array
     */
    protected $_scopePriorityScheme = ['global'];

    /**
     * Path to corresponding XSD file with validation rules for config
     *
     * @var string
     */
    protected $_schemaFile;

    /**
     * @var \Magento\Theme\Model\Theme
     */
    protected $theme;

    /**
     * @var \MageBig\MbFrame\Framework\App\Config\ThemeId
     */
    protected $themeId;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Config\DomFactory
     */
    private $domFactory;

    /**
     * Reader constructor.
     * @param \Magento\Theme\Model\Theme $theme
     * @param \MageBig\MbFrame\Framework\App\Config\ThemeId $themeId
     * @param \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
     * @param \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
     * @param ReadFactory $readFactory
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Config\ConverterInterface $converter
     * @param \Magento\Framework\App\Config\Initial\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\DomFactory $domFactory
     * @param string $fileName
     */
    public function __construct(
        \Magento\Theme\Model\Theme $theme,
        \MageBig\MbFrame\Framework\App\Config\ThemeId $themeId,
        \Magento\Framework\Component\ComponentRegistrar $componentRegistrar,
        \Magento\Framework\Config\FileIteratorFactory $iteratorFactory,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\Config\ConverterInterface $converter,
        \Magento\Framework\App\Config\Initial\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\DomFactory $domFactory,
        $fileName = 'config.xml'
    ) {
        $this->_schemaFile = $schemaLocator->getSchema();
        $this->_fileResolver = $fileResolver;
        $this->_converter = $converter;
        $this->domFactory = $domFactory;
        $this->_fileName = $fileName;
        $this->iteratorFactory = $iteratorFactory;
        $this->theme = $theme;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->themeId = $themeId;
    }

    /**
     * Read configuration scope
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function read()
    {
        $fileList = [];
        foreach ($this->_scopePriorityScheme as $scope) {
            $directories = $this->_fileResolver->get($this->_fileName, $scope);
            foreach ($directories as $key => $directory) {
                $fileList[$key] = $directory;
            }
        }

        $code = $this->themeId->getThemeId();

        if ($code !== null) {
            $directories2 = $this->getConfigurationFiles($this->_fileName);
            foreach ($directories2 as $key2 => $directory2) {
                $fileList[$key2] = $directory2;
            }
        }

        if (!count($fileList)) {
            return [];
        }

        /** @var \Magento\Framework\Config\Dom $domDocument */
        $domDocument = null;
        foreach ($fileList as $file) {
            try {
                if (!$domDocument) {
                    $domDocument = $this->domFactory->createDom(['xml' => $file, 'schemaFile' => $this->_schemaFile]);
                } else {
                    $domDocument->merge($file);
                }
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase("Invalid XML in file %1:\n%2", [$file, $e->getMessage()])
                );
            }
        }

        $output = [];
        if ($domDocument) {
            $output = $this->_converter->convert($domDocument->getDom());
        }

        return $output;
    }

    public function getConfigurationFiles($filename)
    {
        $code = $this->themeId->getThemeId();
        $this->theme->load($code);
        $themePath = $this->theme->getFullPath();

        return $this->iteratorFactory->create($this->getFilesTheme($filename, $themePath));
    }

    /**
     * Go through all modules and find corresponding files of active modules
     *
     * @param string $filename
     * @param string $theme
     * @return array
     */
    private function getFilesTheme($filename, $theme = '')
    {
        $result = [];
        $themeEtcDir = $this->getThemeDir($theme) . '/etc';
        $file = $themeEtcDir . '/' . $filename;
        $directoryRead = $this->readFactory->create($themeEtcDir);
        $path = $directoryRead->getRelativePath($file);
        if ($directoryRead->isExist($path)) {
            $result[] = $file;
        }

        return $result;
    }

    /**
     * @param $theme
     * @return null|string
     */
    private function getThemeDir($theme)
    {
        $path = $this->componentRegistrar->getPath(\Magento\Framework\Component\ComponentRegistrar::THEME, $theme);

        return $path;
    }
}
