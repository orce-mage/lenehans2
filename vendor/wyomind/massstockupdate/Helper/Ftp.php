<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassStockUpdate\Helper;

/**
 * Class Ftp
 * @package Wyomind\MassStockUpdate\Helper
 */
class Ftp extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(\Wyomind\MassStockUpdate\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context);
    }
    /**
     * @param $data
     * @return \Magento\Framework\Filesystem\Io\Ftp|\Magento\Framework\Filesystem\Io\Sftp|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConnection($data)
    {
        $port = $data['ftp_port'];
        $login = $data['ftp_login'];
        $password = $data['ftp_password'];
        $sftp = $data['use_sftp'];
        $active = $data['ftp_active'];
        $host = str_replace(["ftp://", "ftps://"], "", $data["ftp_host"]);
        if ($data['ftp_port'] != "" && $data["use_sftp"]) {
            $host .= ":" . $data['ftp_port'];
        }
        if (isset($data['file_path'])) {
            $fullFilePath = rtrim($data['ftp_dir'], "/") . "/" . ltrim($data['file_path'], "/");
            $fullPath = dirname($fullFilePath);
        } else {
            $fullPath = rtrim($data['ftp_dir'], "/");
        }
        if ($sftp) {
            $ftp = $this->_ioSftp;
        } else {
            $ftp = $this->_ioFtp;
        }
        $ftp->open([
            'host' => $host,
            'port' => $port,
            'user' => $login,
            //ftp
            'username' => $login,
            //sftp
            'password' => $password,
            'timeout' => '10',
            'path' => $fullPath,
            'passive' => !$active,
        ]);
        // sftp doesn't chdir automatically when opening connection
        if ($sftp) {
            $ftp->cd($fullPath);
        }
        return $ftp;
    }
}