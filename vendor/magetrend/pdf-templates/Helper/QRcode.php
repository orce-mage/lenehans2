<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
namespace Magetrend\PdfTemplates\Helper;

/**
 * Qr code generator
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class QRcode
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $curl;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $io;

    /**
     * QRcode constructor.
     *
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Filesystem\Io\File $io
     */
    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Filesystem\Io\File $io
    ) {
        $this->curl = $curl;
        $this->io = $io;
    }

    /**
     * Generate qr code
     *
     * @param $data
     * @param $width
     * @param $height
     * @param bool $pathToFile
     */
    public function png($data, $width = 350, $height = 350, $pathToFile = false)
    {
        $direcoty = $this->getDirectory($pathToFile);
        $fileName = $this->getFileName($pathToFile);

        $this->curl->get('https://chart.googleapis.com/chart?chs='.$width.'x'.$height.'&cht=qr&chl='.urlencode($data).'&choe=UTF-8&chld=L|0');
        $this->io->open(['path'=> $direcoty]);
        $this->io->write(
            $fileName,
            $this->curl->getBody(),
            0644
        );
    }

    /**
     * Removes file name and returns directory
     *
     * @param $pathToFile
     * @return string
     */
    public function getDirectory($pathToFile)
    {
        $directory = explode('/', $pathToFile);
        array_pop($directory);
        $directory = implode('/', $directory);
        return $directory;
    }

    /**
     * Returns file name from path to file
     *
     * @param $pathToFile
     * @return string
     */
    public function getFileName($pathToFile)
    {
        $pathToFile = explode('/', $pathToFile);
        return end($pathToFile);
    }
}
