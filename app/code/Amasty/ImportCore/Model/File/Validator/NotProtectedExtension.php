<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Model\File\Validator;

use Amasty\ImportCore\Api\Source\SourceConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * In M2.4+ XML type was added to validator as protected
 * must remove it for import file upload
 */
class NotProtectedExtension extends \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension
{
    /**
     * @var SourceConfigInterface
     */
    private $sourceConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SourceConfigInterface $sourceConfig
    ) {
        $this->sourceConfig = $sourceConfig;

        parent::__construct($scopeConfig);
    }

    public function getProtectedFileExtensions($store = null)
    {
        $extensions = parent::getProtectedFileExtensions();
        if (is_string($extensions)) {
            $extensions = explode(',', $extensions);
        }

        return $this->unsetImportFileTypes($extensions);
    }

    private function unsetImportFileTypes(array $extensions): array
    {
        $sources = $this->sourceConfig->all();

        foreach ($extensions as $key => $extension) {
            if (isset($sources[$extension])) {
                unset($extensions[$key]);
            }
        }

        return $extensions;
    }
}
