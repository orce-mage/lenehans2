<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassStockUpdate\Helper;

/**
 * Class Storage
 * @package Wyomind\MassStockUpdate\Helper
 */
class Storage extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    public $module = "MassStockUpdate";
    /**
     * @var null
     */
    public $_mageRootDir = null;
    /**
     * @var null
     */
    public $_driverFile = null;
    /**
     * @var \Magento\Framework\Filesystem\Driver\FileFactory|null
     */
    public $_driverFileFactory = null;
    /**
     * @var null|\Wyomind\MassStockUpdate\Filesystem\Driver\HttpFactory
     */
    public $_driverHttpFactory = null;
    /**
     * @var null|\Wyomind\MassStockUpdate\Filesystem\Driver\HttpsFactory
     */
    public $_driverHttpsFactory = null;
    /**
     * @var \Magento\Framework\Filesystem\Io\FtpFactory|null
     */
    public $_ioFtpFactory = null;
    /**
     * @var \Magento\Framework\Filesystem\Io\SftpFactory|null
     */
    public $_ioSftpFactory = null;
    public function __construct(\Wyomind\MassStockUpdate\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context, \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory, \Wyomind\MassStockUpdate\Filesystem\Driver\HttpFactory $driverHttpFactory, \Wyomind\MassStockUpdate\Filesystem\Driver\HttpsFactory $driverHttpsFactory, \Magento\Framework\Filesystem\Io\FtpFactory $ioFtpFactory, \Magento\Framework\Filesystem\Io\SftpFactory $ioSftpFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context);
        $this->_driverFileFactory = $driverFileFactory;
        $this->_driverHttpFactory = $driverHttpFactory;
        $this->_driverHttpsFactory = $driverHttpsFactory;
        $this->_ioFtpFactory = $ioFtpFactory;
        $this->_ioSftpFactory = $ioSftpFactory;
    }
    /**
     * Evaluate the file path as a the regular expression
     * @param $path
     * @param $fileType
     * @param bool $isMultiple
     * @return array|mixed|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function evalRegexp($path, $fileType, $isMultiple = false, $params = [])
    {
        if (empty($path)) {
            return;
        }
        if ($fileType != Data::LOCATION_MAGENTO && $fileType != Data::LOCATION_FTP) {
            if (!$isMultiple) {
                return $path;
            }
            return [$path];
        }
        $files = [];
        if ($fileType == Data::LOCATION_MAGENTO) {
            $driverFile = $this->getDriverFile();
            $path = rtrim($this->getMageRootDir(), "/") . "/" . ltrim($path, "/");
            $directory = substr($path, 0, strrpos($path, "/"));
            $fileInDir = $driverFile->readDirectory($directory);
            $files = [];
            foreach ($fileInDir as $file) {
                $pattern = str_replace(["/", ".", "(", ")"], ["\\/", "\\.", "\\(", "\\)"], $path);
                if (preg_match("#" . $pattern . "#", $file)) {
                    $files[] = str_replace(rtrim($this->getMageRootDir(), "/"), "", $file);
                }
            }
        } elseif ($fileType == Data::LOCATION_FTP) {
            if ($params["use_sftp"]) {
                $ftp = $this->_ioSftpFactory->create();
            } else {
                $ftp = $this->_ioFtpFactory->create();
            }
            $host = str_replace(["ftp://", "ftps://"], "", $params["ftp_host"]);
            if ($params['ftp_port'] != "" && $params["use_sftp"]) {
                $host .= ":" . $params['ftp_port'];
            }
            $fullFilePath = rtrim($params['ftp_dir'], "/") . "/" . ltrim($params['file_path'], "/");
            $fullPath = dirname($fullFilePath);
            $fileName = basename($fullFilePath);
            try {
                $ftp->open([
                    'host' => $host,
                    'user' => $params["ftp_login"],
                    // sftp only
                    'username' => $params["ftp_login"],
                    'port' => $params['ftp_port'],
                    'password' => $params["ftp_password"],
                    'timeout' => '120',
                    'path' => $fullPath,
                    'passive' => !$params["ftp_active"],
                ]);
                $ftp->cd($fullPath);
                $allFiles = $ftp->ls();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
            foreach ($allFiles as $file) {
                if (preg_match(strtolower('#' . $fullFilePath . '#'), strtolower(str_replace(["//", "/./"], "/", $file['id'])))) {
                    $files[] = $file['id'];
                }
            }
        }
        if (count($files)) {
            if (!$isMultiple) {
                return $files[0];
            } else {
                return $files;
            }
        }
        return str_replace(rtrim($this->getMageRootDir(), "/"), "", $path);
    }
    /**
     * Get file driver instance
     * @return null|\Magento\Framework\Filesystem\Driver\File
     */
    public function getDriverFile()
    {
        if ($this->_driverFile == null) {
            $this->_driverFile = $this->_driverFileFactory->create();
        }
        return $this->_driverFile;
    }
    /**
     * Get Mage root dir
     */
    public function getMageRootDir()
    {
        if ($this->_mageRootDir == null) {
            $this->_mageRootDir = $this->_directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        }
        return $this->_mageRootDir;
    }
    /**
     * Retrieve the content of the import file and put it in a tmp file
     * @param array $params
     * @return mixed|string the temp file
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImportFile($params)
    {
        try {
            $class = "\\Wyomind\\" . $this->module . "\\Helper\\Data";
            $tmpFileName = $this->newTempFileName();
            $driverFile = $this->getDriverFile();
            switch ($params["file_system_type"]) {
                case $class::LOCATION_MAGENTO:
                    $newFileName = rtrim($this->getMageRootDir(), "/") . "/" . ltrim($params['file_path'], "/");
                    if (!$driverFile->isExists($newFileName)) {
                        throw new \Exception(__("Magento File System : File %1 not found !", $newFileName));
                    } else {
                        $driverFile->copy($newFileName, $tmpFileName);
                    }
                    break;
                case $class::LOCATION_URL:
                    $content = "";
                    if (strpos($params["file_path"], "http://") !== false) {
                        // HTTP
                        $url = str_replace("http://", "", $params["file_path"]);
                        if (strpos($url, ':') !== false && strpos($url, '@') !== false) {
                            // use curl if a login:password is detected
                            $auth = substr($url, 0, strrpos($url, '@'));
                            $url = substr($url, strrpos($url, '@') + 1);
                            $out = fopen($tmpFileName, 'w');
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_FILE, $out);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_URL, "http://" . $url);
                            curl_setopt($ch, CURLOPT_USERPWD, $auth);
                            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                            $result = curl_exec($ch);
                            $info = curl_getinfo($ch);
                            if ($result !== true || !isset($info['http_code']) || $info['http_code'] != 200) {
                                // if the result isn't true and the http code is not 200, consider the file couldn't be downloaded
                                throw new \Exception(__("HTTP : File %1 not found !", $params["file_path"]));
                            }
                            curl_close($ch);
                            fclose($out);
                        } else {
                            $out = fopen($tmpFileName, 'w');
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_FILE, $out);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            curl_setopt($ch, CURLOPT_URL, $url);
                            $result = curl_exec($ch);
                            $info = curl_getinfo($ch);
                            if (!isset($info['http_code']) || $info['http_code'] != 200) {
                                // if the result isn't true and the http code is not 200, consider the file couldn't be download$
                                throw new \Exception(__("HTTP : File %1 not found !", $params["file_path"]));
                            }
                            curl_close($ch);
                            fclose($out);
                        }
                    } elseif (strpos($params["file_path"], "https://") !== false) {
                        // HTTPS
                        $url = str_replace("https://", "", $params["file_path"]);
                        if (strpos($url, ':') !== false && strpos($url, '@') !== false) {
                            // use curl if a login:password is detected
                            $auth = substr($url, 0, strrpos($url, '@'));
                            $url = substr($url, strrpos($url, '@') + 1);
                            $out = fopen($tmpFileName, 'w');
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_FILE, $out);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_URL, "https://" . $url);
                            curl_setopt($ch, CURLOPT_USERPWD, $auth);
                            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                            $result = curl_exec($ch);
                            $info = curl_getinfo($ch);
                            if ($result !== true || !isset($info['http_code']) || $info['http_code'] != 200) {
                                // if the result isn't true and the http code is not 200, consider the file couldn't be downloaded
                                throw new \Exception(__("HTTP : File %1 not found !", $params["file_path"]));
                            }
                            curl_close($ch);
                            fclose($out);
                        } else {
                            $out = fopen($tmpFileName, 'w');
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_FILE, $out);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                            $result = curl_exec($ch);
                            $info = curl_getinfo($ch);
                            if (!isset($info['http_code']) || $info['http_code'] != 200) {
                                // if the result isn't true and the http code is not 200, consider the file couldn't be download$
                                throw new \Exception(__("HTTPS : File %1 not found !", $params["file_path"]));
                            }
                            curl_close($ch);
                            fclose($out);
                        }
                    }
                    break;
                case $class::LOCATION_FTP:
                    if ($params["use_sftp"]) {
                        $ftp = $this->_ioSftpFactory->create();
                    } else {
                        $ftp = $this->_ioFtpFactory->create();
                    }
                    $host = str_replace(["ftp://", "ftps://"], "", $params["ftp_host"]);
                    if ($params['ftp_port'] != "" && $params["use_sftp"]) {
                        $host .= ":" . $params['ftp_port'];
                    }
                    $fullFilePath = '/' . trim($params['file_path'], '/');
                    $fullPath = dirname($fullFilePath);
                    $fileName = basename($fullFilePath);
                    try {
                        $ftp->open([
                            'host' => $host,
                            'user' => $params["ftp_login"],
                            // sftp only
                            'username' => $params["ftp_login"],
                            'port' => $params['ftp_port'],
                            'password' => $params["ftp_password"],
                            'timeout' => '120',
                            'path' => $fullPath,
                            'passive' => !$params["ftp_active"],
                        ]);
                        $ftp->cd($fullPath);
                        $allFiles = $ftp->ls();
                        $found = false;
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }
                    foreach ($allFiles as $file) {
                        if (str_replace(["//", "/./"], "/", $file['id']) == $fullFilePath) {
                            $found = true;
                        }
                    }
                    if ($found) {
                        $ftp->read($fileName, $tmpFileName);
                        $ftp->close();
                    } else {
                        $ftp->read($fileName, $tmpFileName);
                        $ftp->close();
                        //throw new \Exception(__("FTP : File %1 not found !", $fullFilePath));
                    }
                    break;
                case $class::LOCATION_DROPBOX:
                    $path = $params['file_path'];
                    $token = $params['dropbox_token'];
                    try {
                        $url = "https://content.dropboxapi.com/2/files/download";
                        $headers = ['Authorization: Bearer ' . $token, 'Content-Type: text/plain', 'Dropbox-API-Arg: ' . json_encode(['path' => $path])];
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $content = curl_exec($ch);
                        curl_close($ch);
                        if (strstr(str_replace(["
", "\r", "\t"], '', $content), 'invalid_access_token')) {
                            throw new \Exception($this->__("The token is invalid. It can be generated from your Dropbox account https://www.dropbox.com/developers/apps"));
                        }
                        if ($content === false) {
                            throw new \Exception($this->__("The file '" . $params['file_path'] . "' seems to be empty."));
                        }
                        if (!$content) {
                            throw new \Exception($this->__("The file '" . $params['file_path'] . "' cannot be fetched."));
                        }
                    } catch (\Exception $er) {
                        throw new \Exception(__("DROPBOX : %1", $er->getMessage()));
                    }
                    $driverFile->filePutContents($tmpFileName, $content);
                    break;
                case $class::LOCATION_WEBSERVICE:
                    $url = $params['file_path'];
                    $data = str_replace("{{DATE}}", date('Y-m-d'), $params['webservice_params']);
                    $login = $params['webservice_login'];
                    $password = $params['webservice_password'];
                    $content = "";
                    try {
                        $fields = ['login' => urlencode($login), 'password' => urlencode($password), 'data' => base64_encode(gzencode($data))];
                        $fields_string = "";
                        foreach ($fields as $key => $value) {
                            $fields_string .= $key . '=' . $value . '&';
                        }
                        rtrim($fields_string, '&');
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, count($fields));
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $content = curl_exec($ch);
                        curl_close($ch);
                    } catch (\Exception $er) {
                        throw new \Exception(__("WEB SERVICE : %1", $er->getMessage()));
                    }
                    $driverFile->filePutContents($tmpFileName, $content);
                    break;
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Source file `%1` doesn't exist (%2). Please check the source file path. ", $params['file_path'], $e->getMessage()));
        }
        return $tmpFileName;
    }
    /**
     * @return mixed|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function newTempFileName()
    {
        $class = "\\Wyomind\\" . $this->module . "\\Helper\\Data";
        $tmpFolder = $this->getMageRootDir() . $class::TMP_FOLDER;
        $this->mkdir($tmpFolder);
        $tempFileName = tempnam($tmpFolder, $class::TMP_FILE_PREFIX) . '.' . $class::TMP_FILE_EXT;
        if (strpos($tempFileName, ".orig") !== false) {
            $this->deleteFile(dirname($tempFileName), basename($tempFileName));
            $tempFileName = str_replace(".orig", "", $tempFileName);
        }
        return $tempFileName;
    }
    /**
     * Create a folder
     * @param string $folder
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function mkdir($folder)
    {
        $driverFile = $this->getDriverFile();
        $driverFile->createDirectory($folder, 0755);
    }
    /**
     * Delete a file
     * @param string $folder
     * @param string $file
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteFile($folder, $file)
    {
        $driverFile = $this->getDriverFile();
        if ($driverFile->isExists($folder . "/" . $file)) {
            $driverFile->deleteFile($folder . '/' . $file);
        }
    }
    /**
     * @param $folderFrom
     * @param $fileFrom
     * @param $folderTo
     * @param $fileTo
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function moveFile($folderFrom, $fileFrom, $folderTo, $fileTo)
    {
        $driverFile = $this->getDriverFile();
        if ($driverFile->isExists($folderFrom . "/" . $fileFrom)) {
            $this->mkdir($folderTo);
            $driverFile->rename($folderFrom . DIRECTORY_SEPARATOR . $fileFrom, $folderTo . DIRECTORY_SEPARATOR . $fileTo);
        }
    }
    /**
     * Post process acton on FTP files
     * @param array $params
     * @param string $action
     * @throws \Exception
     */
    public function ftpFileAction($params, $action = 'move')
    {
        $folderFrom = $params['ftp_dir'];
        $folderTo = $params['post_process_move_folder'];
        $fullFilePathFrom = '/' . trim($params['file_path'], '/');
        $fullFilePathFrom = str_replace(["//", "/./"], "/", $fullFilePathFrom);
        $fullFilePathTo = $folderTo . DIRECTORY_SEPARATOR;
        if ($params['use_sftp']) {
            $ftp = $this->_ioSftpFactory->create();
        } else {
            $ftp = $this->_ioFtpFactory->create();
        }
        $host = str_replace(['ftp://', 'ftps://'], '', $params['ftp_host']);
        if ($params['ftp_port'] != "" && $params['use_sftp']) {
            $host .= ":" . $params['ftp_port'];
        }
        try {
            $ftp->open([
                'host' => $host,
                'user' => $params['ftp_login'],
                // sftp only
                'username' => $params['ftp_login'],
                'port' => $params['ftp_port'],
                'password' => $params['ftp_password'],
                'timeout' => '120',
                'path' => $folderFrom,
                'passive' => !$params['ftp_active'],
            ]);
            $ftp->cd($folderFrom);
            $allFiles = $ftp->ls();
            foreach ($allFiles as $file) {
                if (preg_match(strtolower('#' . $fullFilePathFrom . '#'), strtolower(str_replace(["//", "/./"], "/", $file['id'])))) {
                    if ('move' === $action) {
                        $ftp->mv($file['id'], $fullFilePathTo . basename($file['id']));
                    } elseif ('delete' === $action) {
                        $ftp->rm($fullFilePathFrom);
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * Open file
     * @param string $folder
     * @param string $file
     * @param string $mode
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function fileOpen($folder, $file, $mode = 'w')
    {
        $driverFile = $this->getDriverFile();
        $resource = $driverFile->fileOpen($folder . "/" . $file, $mode);
        return $resource;
    }
    /**
     * @param $resource
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function fileClose($resource)
    {
        $driverFile = $this->getDriverFile();
        $result = $driverFile->fileClose($resource);
        return $result;
    }
    /**
     * @param $resource
     * @param $data
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function fileWrite($resource, $data)
    {
        $driverFile = $this->getDriverFile();
        $result = $driverFile->fileWrite($resource, $data);
        return $result;
    }
    /**
     * @param $resource
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function fileReadLine($resource)
    {
        $driverFile = $this->getDriverFile();
        $result = $driverFile->fileReadLine($resource, 1024000, "
");
        return $result;
    }
    /**
     * Put data in a csv file
     * @param resource $resource
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function filePutCsv($resource, $data, $delimiter = ",", $enclosure = "")
    {
        if ($enclosure == "none") {
            $enclosure = "";
        }
        $driverFile = $this->getDriverFile();
        if ($enclosure == "") {
            $driverFile->filePutCsv($resource, $data, $delimiter);
        } else {
            $driverFile->filePutCsv($resource, $data, $delimiter, $enclosure);
        }
    }
}