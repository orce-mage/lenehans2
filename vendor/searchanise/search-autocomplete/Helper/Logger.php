<?php

namespace Searchanise\SearchAutocomplete\Helper;

use Searchanise\SearchAutocomplete\Helper\Data as DataHelper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\PhpEnvironment\Response as HttpResponse;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Escaper;

/**
 * Searchanise logger
 */
class Logger extends AbstractHelper
{
    const TYPE_ERROR   = 'Error';
    const TYPE_INFO    = 'Info';
    const TYPE_WARNING = 'Warning';
    const TYPE_DEBUG   = 'Debug';

    private static $allowedTypes = [
        self::TYPE_ERROR,
        self::TYPE_INFO,
        self::TYPE_WARNING,
        self::TYPE_DEBUG,
    ];

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @ Escaper
     */
    private $escaper;

    /**
     * @var HttpResponse
     */
    private $response = null;

    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        Escaper $escaper
    ) {
        $this->dataHelper = $dataHelper;
        $this->escaper = $escaper;

        parent::__construct($context);
    }

    /**
     * Log message
     */
    public function log()
    {
        $args = func_get_args();
        $message = [];
        $type = array_pop($args);

        // Check log type
        if (!in_array($type, self::$allowedTypes)) {
            if ($type !== null) {
                array_push($args, $type);
            }

            $type = self::TYPE_ERROR;
        }

        if ($type == self::TYPE_DEBUG && !$this->dataHelper->checkDebug(true)) {
            return false;
        }

        // Check log message
        if (!empty($args)) {
            foreach ($args as $k => $v) {
                if (!is_array($v) && preg_match('~[^\x20-\x7E\t\r\n]~', $v) > 0) {
                    $message[] = '=== BINARY DATA ===';
                } else {
                    $message[] = print_r($v, true);
                }
            }
        }
        $message = implode("\n", $message);

        switch ($type) {
            case self::TYPE_ERROR:
                $this->_logger->error('Searchanise #' . $message);
                break;
            case self::TYPE_WARNING:
                $this->_logger->warning('Searchanise #' . $message);
                break;
            case self::TYPE_DEBUG:
                $this->_logger->debug('Searchanise #' . $message);
                break;
            default:
                $this->_logger->info('Searchanise #' . $message);
        }

        if ($this->dataHelper->checkDebug(true)) {
            call_user_func_array([$this, 'printR'], $args);
        }

        return true;
    }

    public function setResponseContext(HttpResponse $httpResponse = null)
    {
        $this->response = $httpResponse;
        return $this;
    }

    public function printR()
    {
        static $count = 0;

        $args = func_get_args();
        $content = '';
        $time = date('c');

        if (!empty($args)) {
            $content .= '<ol style="font-family: Courier; font-size: 12px; border: 1px solid #dedede; background-color: #efefef; float: left; padding-right: 20px;">';
            $content .= '<li><pre>===== ' . $time . '===== </pre></li>' . "\n";

            foreach ($args as $k => $v) {
                $v = $this->escaper->escapeHtml(print_r($v, true));
                if ($v == '') {
                    $v = '    ';
                }

                $content .= '<li><pre>' . $v . "\n" . '</pre></li>';
            }

            $content .= '</ol><div style="clear:left;"></div>';
        }

        $count++;

        if (!empty($content) && !empty($this->response)) {
            $this->response->appendBody($content);
        }
    }
}
