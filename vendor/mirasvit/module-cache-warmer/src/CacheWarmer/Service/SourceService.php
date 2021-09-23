<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */




namespace Mirasvit\CacheWarmer\Service;


use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Xml\Parser;
use Mirasvit\CacheWarmer\Api\Data\SourceInterface;
use Mirasvit\CacheWarmer\Api\Data\UserAgentInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\SourceRepositoryInterface;
use Psr\Log\LoggerInterface;

class SourceService
{
    private $sourceRepository;

    private $xmlParser;

    private $filesystem;

    private $pageRepository;

    private $logger;

    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        Parser $parser,
        Filesystem $filesystem,
        PageRepositoryInterface $pageRepository,
        LoggerInterface $logger
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->xmlParser        = $parser;
        $this->filesystem       = $filesystem;
        $this->pageRepository   = $pageRepository;
        $this->logger           = $logger;
    }

    /**
     * @param int|null $sourceId
     *
     * @return array
     */
    public function exportUrls($sourceId = null)
    {
        $urlsBySource = [];

        $sources = $sourceId ? [$this->sourceRepository->get($sourceId)] : $this->sourceRepository->getCollection();

        foreach ($sources as $source) {
            if (!$source || !$source->getIsActive()) {
                continue;
            }

            $parsed = $this->parseSource($source);

            if ($parsed && is_array($parsed)) {
                $urlsBySource[$source->getId()] =  $parsed;
            }
        }

        return $urlsBySource;
    }

    /**
     * @param SourceInterface $source
     *
     * @return array|false
     */
    private function parseSource(SourceInterface $source)
    {
        if (!$this->shouldParseSource($source)) {
            return false;
        }

        switch ($source->getSourceType()) {
            case SourceInterface::TYPE_SITEMAP:
                $urls = $this->parseSitemap($source);
                $urls['user_agent'] = UserAgentInterface::SITEMAP_USER_AGENT;

                return $urls;
            case SourceInterface::TYPE_FILE:
                $urls = $this->parseFile($source);
                $urls['user_agent'] = UserAgentInterface::FILE_USER_AGENT;

                return $urls;
            case SourceInterface::TYPE_CRAWLER:
                $pageCollection = $this->pageRepository
                    ->getCollection()
                    ->addFieldToFilter('user_agent', UserAgentInterface::DESKTOP_USER_AGENT);

                foreach ($pageCollection as $page) {
                    $page->setSourceId($source->getId());
                    $this->pageRepository->save($page);
                }

                $source->setLastSyncronizedAt(date("Y-m-d H:i:s"));
                $this->sourceRepository->save($source);

                return false;
            default:
                return false;
        }
    }

    /**
     * @param SourceInterface $source
     *
     * @return array
     */
    private function parseSitemap(SourceInterface $source)
    {
        $absolutePath = $this->getSourceAbsolutePath($source->getPath());

        $urls   = [];
        $parsed = [];

        try {
            $parsed = $this->xmlParser->load($absolutePath)->xmlToArray();
        } catch (\Exception $e) {
            // resolve exceptions related to & in sitemap
            if (preg_match('/DOMDocument::load\(\): Entity \'[^\']*\' not defined in/is', $e->getMessage())) {
                $absoluteTmpPath = $absolutePath . '.tmp';
                $content = file_get_contents($absolutePath);
                $newContent = str_replace('&', urlencode('&'), $content);

                $file = fopen($absoluteTmpPath, 'a+');
                fwrite($file, $newContent);

                $parsed = $this->xmlParser->load($absoluteTmpPath)->xmlToArray();

                fclose($file);
                unlink($absoluteTmpPath);
            } else {
                $this->logger->error($e->getMessage());
            }
        }

        if (!count($parsed)) {
            return $urls;
        }

        if (isset($parsed['sitemapindex'])) { // parse segmented sitemap
            foreach ($parsed['sitemapindex']['sitemap'] as $sitemap) {
                $sitemapLink = $sitemap['loc'];

                if (strpos($sitemapLink, '.xml') === false) {
                    // in case .gz archives are used for inner sitemaps
                    // Magento not generating such sitemaps but some 3rd-party extensions might
                    continue;
                }

                $routes          = explode('/', $sitemapLink);
                $sitemapFileName = $routes[count($routes) - 1];
                $newRoutes       = explode('/', $source->getPath());

                $newRoutes[count($newRoutes) - 1] = $sitemapFileName;

                $newAbsPath = implode('/', $newRoutes);
                $newSource  = $this->sourceRepository->create();
                $newSource->setPath($newAbsPath);

                $urls = array_merge($urls, $this->parseSitemap($newSource));
            }
        }

        if (isset($parsed['urlset'])) {
            $urls = array_merge($urls, $this->retriveUrlsFromArray($parsed['urlset']));
        }

        return $urls;
    }

    /**
     * @param SourceInterface $source
     *
     * @return array
     */
    private function parseFile(SourceInterface $source)
    {
        $absolutePath = $this->getSourceAbsolutePath($source->getPath());

        $urls = explode(PHP_EOL, file_get_contents($absolutePath));
        $urls = array_filter(array_map('trim', $urls));

        return $urls;
    }

    /**
     * @param string $url
     * @param int    $sourceId
     */
    public function resolveSource($url, $sourceId)
    {
        $collection = $this->pageRepository->getCollection()->addFieldToFilter( "uri_hash", sha1($url));

        foreach ($collection as $page) {
            if ($page->getSourceId() == $sourceId) {
                continue;
            }
            // decide which sources shoul be changed
            $page->setSourceId($sourceId);
            $this->pageRepository->save($page);
        }
    }

    /**
     * @param array $urls
     * @param int   $sourceId
     */
    public function cleanup(array $urls, $sourceId)
    {
        $collection = $this->pageRepository->getCollection()->addFieldToFilter('source_id', $sourceId);

        foreach ($collection as $page) {
            if(in_array($page->getUri(), $urls)) {
                continue;
            }

            // if url from the removed source has visits - reassign page to the default source
            // otherwise - delete
            if ($page->getPopularity() > 0) {
                $page->setSourceId(SourceInterface::DEFAULT_SOURCE_ID);
                $this->pageRepository->save($page);
            } else {
                $this->pageRepository->delete($page);
            }
        }
    }

    /**
     * @param array $array
     * @return array
     */
    private function retriveUrlsFromArray(array $array)
    {
        $urls = [];

        foreach ($array as $item) {
            if (isset($item['loc'])) {
                $urls[] = $item['loc'];
            } elseif (is_array($item)) {
                $urls = array_merge($urls, $this->retriveUrlsFromArray($item));
            }
        }

        return $urls;
    }

    /**
     * @return SourceRepositoryInterface
     */
    public function getSourceRepository()
    {
        return $this->sourceRepository;
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    public function getSourceAbsolutePath($relativePath)
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath() . $relativePath;
    }

    /**
     * @param SourceInterface $source
     * @param bool $exit
     *
     * @return bool
     */
    private function shouldParseSource(SourceInterface $source, $exit = false)
    {
        $absolutePath = $this->getSourceAbsolutePath($source->getPath());

        if (!file_exists($absolutePath) && !is_readable($absolutePath)) {
            if ($source->getSourceType() == SourceInterface::TYPE_SITEMAP && !$exit) {
                //in some cases sitemaps placed in pub folder
                // but "pub" part not included in path
                $source->setPath('/pub' . $source->getPath());
                
                return $this->shouldParseSource($source, true);
            }

            return false;
        }

        $lastSyncAt   = $source->getLastSyncronizedAt();
        $lastModified = date("Y-m-d H:i:s", filemtime($absolutePath));

        if ($lastSyncAt >= $lastModified) { // parse only sources updated after the last sync
            return false;
        }

        return true;
    }
}
