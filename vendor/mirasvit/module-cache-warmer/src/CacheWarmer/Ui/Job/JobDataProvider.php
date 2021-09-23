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



namespace Mirasvit\CacheWarmer\Ui\Job;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Mirasvit\CacheWarmer\Api\Data\JobInterface;
use Mirasvit\CacheWarmer\Logger\Logger;
use Mirasvit\Core\Service\SerializeService;

class JobDataProvider extends DataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $result = [];

        $result['items'] = [];

        /** @var JobInterface $job */
        foreach ($searchResult->getItems() as $job) {
            $itemData = $job->getData();

            $itemData['trace'] = $this->arrayToTable('_trace', $job->getTrace());
            $itemData['info'] = $this->arrayToTable('_info', $job->getInfo());
            $itemData['filter'] = $this->arrayToTable('_filter', $job->getFilter());

            $result['items'][] = $itemData;
        }

        $result['totalRecords'] = $searchResult->getTotalCount();

        return $result;
    }

    /**
     * @param string $class
     * @param array $data
     * @return string
     */
    private function arrayToTable($class, $data)
    {
        $html = '';

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value[0])) {
                    $value = implode(' → ', $value);
                } else {
                    $value = $this->arrayToTable($class, $value);
                }
            } else {
                $chunks = explode(Logger::DELIMITER, $value);
                foreach ($chunks as $idx => $chunk) {
                    try {
                        // fix: Warning Invalid argument supplied for foreach()
                        $chunkDecoded = SerializeService::decode($chunk);
                        $chunks[$idx] = is_array($chunkDecoded)
                            ? $this->arrayToTable($class, $chunkDecoded)
                            : htmlspecialchars(strip_tags($chunk));
                    } catch (\Exception $e) {
                        $chunk = htmlspecialchars(strip_tags($chunk));
                        $chunks[$idx] = $chunk;
                    }
                }

                $value = implode(' ', $chunks);
            }

            if ($value) {
                $c = '_' . preg_replace("/[^A-Z]/i", '', strtolower($key));
                $html .= "<tr class='$c'>";
                $html .= "<td>$key</td>";
                $html .= "<td>$value</td>";
                $html .= "</tr>";
            }
        }

        if ($html) {
            return "<div class='mst-cache-warmer__job-listing-array $class'><table>$html</table></div>";
        }

        return '';
    }
}
