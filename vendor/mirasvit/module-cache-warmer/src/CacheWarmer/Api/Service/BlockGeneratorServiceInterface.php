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



namespace Mirasvit\CacheWarmer\Api\Service;

use Magento\Framework\App\ResponseInterface;

interface BlockGeneratorServiceInterface
{
    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function prepareContent(ResponseInterface $response);

    /**
     * @param string            $html
     * @param ResponseInterface $response
     * @param array             $container
     * @return ResponseInterface
     */
    public function applyToContent($html, $response, $container);

    /**
     * @param array $container
     * @return string
     */
    public function generateBlock($container);

    /**
     * @param ResponseInterface $response
     * @return void
     */
    public function registerData($response);
}