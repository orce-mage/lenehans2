<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Helper;

/**
 * Class Progress
 * @package Wyomind\Framework\Helper
 */
class Progress extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * DEBUG MODE ENABLED?
     */
    const DEBUG = false;

    /**
     * @var string
     */
    const SUCCEEDED = 'SUCCEEDED';

    /**
     * @var string
     */
    const PENDING = 'PENDING';

    /**
     * @var string
     */
    const PROCESSING = 'PROCESSING';

    /**
     * @var string
     */
    const WARNING = 'WARNING';

    /**
     * @var string
     */
    const HOLD = 'HOLD';

    /**
     * @var string
     */
    const FAILED = 'FAILED';

    /**
     * @var string
     */
    const ERROR = 'ERROR';

    /**
     * @var string
     */
    private $_flagFile;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $_ioWrite;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $_ioRead;

    /**
     * {@inherit}
     */
    private $logger;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * Module name
     * @var string
     */
    private $module;

    /**
     * Path to the tmp directory
     * @var null
     */
    private $tempDirectory;

    /**
     * Prefix of the flag name
     * @var null
     */
    private $filePrefix;

    /**
     * Is log file enabled
     * @var bool
     */
    private $_logEnabled = true;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * Progress constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param string $module Name of the module (camelcase)
     * @param string $tempDirectory Temp directory where to store the flags
     * @param string $filePrefix Prefix of the flag files
     * @param string $loggerClassName Name of the logger class (without namespace, refer to the virutal type @see Logger component)
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        $module = "Framework",
        $tempDirectory = "var/tmp/wyomind",
        $filePrefix = "item_",
        $loggerClassName = "Logger\Logger"
    ) {
    
        $this->filesystem = $filesystem;
        $this->objectManager = $objectManager;

        $this->_ioWrite = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->_ioRead = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);

        $this->logger = $objectManager->create("\Wyomind\\" . $module . "\\" . $loggerClassName);

        $this->directoryList = $directoryList;
        $this->module = $module;
        $this->tempDirectory = $tempDirectory;
        $this->filePrefix = $filePrefix;
        $this->dateTime = $dateTime;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getAbsoluteRootDir()
    {
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
    }

    /**
     * @param bool $logEnabled Activate the log file as defined in the di.xml
     * @param string $fileSuffix Suffix of the flag file
     * @param string $label Label for the process
     * @param bool $byPassRuningProcess Ignore the notification if the process is already running
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function startObservingProgress($logEnabled = true, $fileSuffix = 'progress', $label = 'profile', $byPassRuningProcess = false)
    {
        $this->_logEnabled = $logEnabled;

        $line = $this->readFlag($fileSuffix);
        if (!$byPassRuningProcess) {
            if ($line["status"] === self::PROCESSING) {
                $this->flagUpdate(self::ERROR . ";" . __('"%1" is already processing. Please wait the end of the process.', $label) . ";0");

//                if (php_sapi_name() === 'cli') {
//
//                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('"%1" is already processing. Please wait the end of the process.', $label));
//                }
            }
        }
    }

    /**
     * Stop progress observing
     */
    public function stopObservingProgress()
    {
        set_error_handler(
            function () {
                return false;
            }
        );
        register_shutdown_function(
            function () {
                return false;
            }
        );
    }

    /**
     * @param string $file
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getFlagFile($file = "progress")
    {
        $flagFile = $this->getAbsoluteRootDir() . $this->tempDirectory . $this->filePrefix . $file . ".flag";

        if (!file_exists($flagFile)) {
            $this->_ioWrite->create($this->tempDirectory); // create path if not exists
            $io = $this->_ioRead->openFile($flagFile, "w+");
            $this->_ioRead->writeFile($flagFile, self::PENDING . ";;0");
            $io->close(); // close
        }

        return $flagFile;
    }

    /**
     * @param string $file
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getStats($file = "progress")
    {
        $this->_flagFile = $this->getFlagFile($file);
        $io = $this->_ioWrite->openFile($this->_flagFile, "r");
        $io->close(); // close

        return $io->stat();
    }

    /**
     * @param string $file
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function readFlag($file = "progress")
    {
        if (self::DEBUG) {
            return;
        }
        $this->_flagFile = $this->getFlagFile($file);
        $io = $this->_ioWrite->openFile($this->_flagFile, "r");
        $line = $io->readCsv(0, ";");

        $status = null;
        if (isset($line[0])) {
            $status = $line[0];
        }
        $message = null;
        if (isset($line[1])) {
            $message = $line[1];
        }
        $percent = 0;
        if (isset($line[2])) {
            $percent = $line[2];
        }
        $io->close(); // close

        return ["status" => $status, "message" => $message, "percent" => $percent];
    }

    /**
     * Update the flag file
     * @param $content
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function flagUpdate($content)
    {
        if (self::DEBUG) {
            return;
        }
        $io = $this->_ioRead->openFile($this->_flagFile, "w+");
        $this->_ioRead->writeFile($this->_flagFile, $content);
        $io->close(); // close

        if (php_sapi_name() === 'cli') {
            $time = $this->dateTime->date('Y-m-d H:i:s');
            $exploded = explode(";", $content);
            $status = $exploded[0];
            $message = $exploded[1];

            $percent = floor($exploded[2] / 2);
            if ($status == self::SUCCEEDED) {
                $status = "\e[92m" . $status . "\e[39m";
                fwrite(STDOUT, sprintf("\r %s %-20s [%-" . $percent . "s>%-" . (50 - $percent) . "s] %s%% : %-100s", $time, $status, str_pad("", $percent, "~"), str_pad("", 50 - $percent, " "), $percent * 2, $message));
            } elseif ($status == self::FAILED) {
                $status = "\e[91m\033[1m" . $status . "\033[0m\e[39m";
                fwrite(STDOUT, sprintf("\r %s %-20s : %-100s", $time, $status, $message));
            } elseif ($status == self::ERROR) {
                $status = "\e[91m\033[1m" . $status . "\033[0m\e[39m";
                fwrite(STDOUT, sprintf("\r %s %-20s : %-100s", $time, $status, $message));
            } elseif ($status == self::WARNING) {
                $status = "\e[95m\033[1m" . $status . "\033[0m\e[39m";
                fwrite(STDOUT, sprintf("\r %s %-20s [%-" . $percent . "s>%-" . (50 - $percent) . "s] %s%% : %-100s", $time, $status, str_pad("", $percent, "~"), str_pad("", 50 - $percent, " "), $percent * 2, $message));
            } elseif ($status == self::PROCESSING) {
                $status = "\e[93m\033[1m" . $status . "\033[0m\e[39m";
                fwrite(STDOUT, sprintf("\r %s %-20s [%-" . $percent . "s>%-" . (50 - $percent) . "s] %s%% : %-100s", $time, $status, str_pad("", $percent, "~"), str_pad("", 50 - $percent, " "), $percent * 2, $message));
            } else {
                $status = "\e[93m\033[1m" . self::PROCESSING . "\033[0m\e[39m";
                $message = str_replace(">", "", trim($content));
                fwrite(STDOUT, sprintf("\r %s %-20s : %-100s", $time, $status, $message));
            }
            fflush(STDOUT);
        }
    }

    /**
     * Catch the shutdown
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function shutdown()
    {
        if ($error = error_get_last()) {
            if (stripos($error['message'], "mcrypt") === false) {
                $this->logOnFail($error["message"]);
            }
        }
        $this->stopObservingProgress();
    }

    /**
     * Add log on failure
     * @param $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function logOnFail($message)
    {
        $this->log($message, true, "FAILED");
    }

    /**
     * Write the log file
     * @param string $message Message associated to the current state
     * @param bool $updateFlag Update the flag file or not
     * @param string $status Current state (constant)
     * @param int $percentage Progression in percentage
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function log($message, $updateFlag = true, $status = 'PROCESSING', $percentage = 0)
    {
        if ($this->_logEnabled) {
            $this->logger->notice($message);
        }
        if ($updateFlag) {
            $this->flagUpdate($status . ";" . $message . ";" . $percentage);
        }
    }
}
