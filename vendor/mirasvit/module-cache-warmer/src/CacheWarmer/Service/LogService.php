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

use Magento\Framework\Stdlib\DateTime;
use Mirasvit\CacheWarmer\Api\Repository\LogRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Service\LogServiceInterface;

class LogService implements LogServiceInterface
{
    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * LogService constructor.
     * @param LogRepositoryInterface $logRepository
     */
    public function __construct(
        LogRepositoryInterface $logRepository
    ) {
        $this->logRepository = $logRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function log($uri, $responseTime, $isHit)
    {
        if (!$uri || $responseTime <= 0) {
            return true;
        }

        $log = $this->logRepository->create()
            ->setResponseTime($responseTime)
            ->setIsHit($isHit)
            ->setUri($uri)
            ->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        $this->logRepository->save($log);

        return true;
    }
}
