<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Utils;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Source;

class StaticViewFileResolver
{
    const DEFAULT_MODULE_NAME = 'Amasty_ExportCore';

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ConfigInterface
     */
    private $routeConfig;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var array
     */
    private $cacheFileId = [];

    public function __construct(
        Repository $assetRepo,
        RequestInterface $request,
        ConfigInterface $routeConfig,
        Source $source
    ) {
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->routeConfig = $routeConfig;
        $this->source = $source;
    }

    /**
     * Getter ID for static view file by current Module Name
     *
     * @param string $fileId
     * @return string
     */
    public function getFileId($fileId): string
    {
        if (isset($this->cacheFileId[$fileId])) {
            return $this->cacheFileId[$fileId];
        }

        $processedFileId = $fileId;
        $fileInfo = $this->assetRepo->extractModule($fileId);
        $modules = $this->getListModules();

        foreach ($modules as $module) {
            $moduleFileId = $module . Repository::FILE_ID_SEPARATOR . $fileInfo[1];

            try {
                $asset = $this->assetRepo->createAsset($moduleFileId);
            } catch (\Exception $e) {
                continue;
            }

            if ($this->source->getFile($asset) === false) {
                continue;
            }

            $processedFileId = $moduleFileId;
            break;
        }

        return $this->cacheFileId[$fileId] = $processedFileId;
    }

    /**
     * Getting a list of modules for processing
     *
     * @return array
     */
    private function getListModules(): array
    {
        $moduleName = $this->request->getModuleName();
        $modules = $this->routeConfig->getModulesByFrontName($moduleName);
        $modules[] = self::DEFAULT_MODULE_NAME;

        return $modules;
    }
}
