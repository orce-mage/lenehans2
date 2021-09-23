<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Icon;

use Amasty\Stockstatus\Api\Data\IconInterface;
use Amasty\Stockstatus\Api\Icon\GetNewInterface;
use Amasty\Stockstatus\Model\IconFactory;

class GetNew implements GetNewInterface
{
    /**
     * @var IconFactory
     */
    private $iconFactory;

    public function __construct(IconFactory $iconFactory)
    {
        $this->iconFactory = $iconFactory;
    }

    public function execute(): IconInterface
    {
        return $this->iconFactory->create();
    }
}
