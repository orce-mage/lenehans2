<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling\FieldModifier;

use Amasty\Base\Model\Serializer;
use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;

class Unserialize implements FieldModifierInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        Serializer $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function transform($value)
    {
        return $this->serializer->unserialize($value) ?: $value;
    }
}
