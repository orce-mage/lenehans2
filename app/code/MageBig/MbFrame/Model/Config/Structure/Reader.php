<?php
/**
 * Backend System Configuration reader.
 * Retrieves system configuration form layout from system.xml files. Merges configuration and caches it.
 *
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\MbFrame\Model\Config\Structure;

use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;

/**
 * Class Reader.
 */
class Reader extends \Magento\Config\Model\Config\Structure\Reader
{
    /**
     * Reader constructor.
     * @param \MageBig\MbFrame\Framework\App\Config\FileResolver $fileResolver
     * @param \Magento\Config\Model\Config\Structure\Converter   $converter
     * @param \Magento\Config\Model\Config\SchemaLocator         $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param CompilerInterface                                  $compiler
     * @param string                                             $fileName
     * @param array                                              $idAttributes
     * @param string                                             $domDocumentClass
     * @param string                                             $defaultScope
     */
    public function __construct(
        \MageBig\MbFrame\Framework\App\Config\FileResolver $fileResolver,
        \Magento\Config\Model\Config\Structure\Converter $converter,
        \Magento\Config\Model\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        CompilerInterface $compiler,
        $fileName = 'magebig_system.xml',
        $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
        $defaultScope = 'global'
    ) {
        $this->compiler = $compiler;
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $compiler,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
