<?php
// @codingStandardsIgnoreFile

namespace MageBig\SyntaxCms\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Logging
 * @package MageBig\SyntaxCms\Helper
 */
class Logging extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * File
     *
     * @var File
     */
    private $file;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Filesystem Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Logging constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param DirectoryList $directoryList
     * @param File $file
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DirectoryList $directoryList,
        File $file,
        ObjectManagerInterface $objectManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->directoryList = $directoryList;
        $this->file = $file;
        parent::__construct(
            $context
        );
    }

    /**
     * @param $bytes
     * @return string
     */
    public function byteconvert($bytes)
    {
        $symbol = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $exp = floor(log($bytes) / log(1024));
        if (!$bytes) {
            return sprintf('%.2f ' . $symbol[0], $bytes);
        }
        return sprintf('%.2f ' . $symbol[(int)$exp], $bytes / pow(1024, floor($exp)));
    }

    /**
     * @return array
     */
    public function getIntervallAsOptions()
    {
        return [
            '0' => __('No update'),
            '5000' => '5s',
            '10000' => '10s',
            '30000' => '30s',
        ];
    }

    /**
     * @return array
     */
    public function getLinesAsOptions()
    {
        return [
            '10' => '10',
            '50' => '50',
            '100' => '100',
            '200' => '200',
            '500' => '500',
            '1000' => '1000',
        ];
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getLogFielsAsOptions()
    {
        $options = ['' => __('-- Please Select --')];
        $logDir = $this->directoryList->getPath(DirectoryList::LOG);
        if ($this->file->isExists($logDir)) {
            $contents = $this->file->readDirectoryRecursively($logDir);
            foreach ($contents as $path) {
                if ($this->file->isFile($path)) {
                    $partsOfPath = substr($path, strlen($logDir) + 1);
                    $bytes = filesize($path);
                    $options[$partsOfPath] = $partsOfPath . " (" . $this->byteconvert($bytes) . ")";
                }
            }
        }
        return $options;
    }

    /**
     * @param $path
     * @param $line_count
     * @param int $block_size
     * @return array
     */
    protected function last_lines($path, $line_count, $block_size = 1024)
    {
        $lines = array();

        // we will always have a fragment of a non-complete line
        // keep this in here till we have our next entire line.
        $leftover = "";
        $fh = fopen($path, 'r');
        // go to the end of the file
        fseek($fh, 0, SEEK_END);
        $maxIteration = 100;
        do {
            // need to know whether we can actually go back
            // $block_size bytes
            $can_read = $block_size;
            if (ftell($fh) < $block_size) {
                $can_read = ftell($fh);
            }

            // go back as many bytes as we can
            // read them to $data and then move the file pointer
            // back to where we were.
            fseek($fh, -$can_read, SEEK_CUR);
            $data = fread($fh, $can_read);
            $data .= $leftover;
            fseek($fh, -$can_read, SEEK_CUR);

            // split lines by \n. Then reverse them,
            // now the last line is most likely not a complete
            // line which is why we do not directly add it, but
            // append it to the data read the next time.
            $split_data = array_reverse(explode("\n", $data));
            $new_lines = array_slice($split_data, 0, -1);
            $lines = array_merge($lines, $new_lines);
            $leftover = $split_data[count($split_data) - 1];
            $maxIteration--;
        } while (count($lines) < $line_count && ftell($fh) != 0 && $maxIteration > 0);
        if (ftell($fh) == 0) {
            $lines[] = $leftover;
        }
        fclose($fh);
        // Usually, we will read too many lines, correct that here.
        return array_reverse(array_slice($lines, 0, $line_count));
    }

    /**
     * @param $file
     * @param int $lines
     * @return \Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function SyntaxCms($file, $lines = 10)
    {
        if ($lines <= 0) {
            $lines = 10;
        }
        $logDir = $this->directoryList->getPath(DirectoryList::LOG);
        if (!$file) {
            return '';
        }
        $file = $logDir . '/' . $file;
        if ($this->file->isExists($file) && $this->file->isFile($file)) {
            if (filesize($file)) {
                $result = array();
                foreach ($this->last_lines($file, $lines) as $line) {
                    $line = htmlentities($line);
                    $line = preg_replace('/^(\[[^]]*\])(.*)$/', '<span style="color:#1569b3;">$1</span><b>$2</b>',
                        $line);
                    $result[] = $line;
                }
                return implode("\n", $result);
            }

            return __('File is empty');
        }
        return __('File not found');
    }

}
