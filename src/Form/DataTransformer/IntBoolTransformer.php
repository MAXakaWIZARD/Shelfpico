<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class IntBoolTransformer implements DataTransformerInterface
{
    /**
     * Transforms an int value to bool
     *
     * @param  int $intValue
     * @return bool
     */
    public function transform($intValue)
    {
        return (bool) $intValue;
    }

    /**
     * Transforms a bool to int
     *
     * @param  bool $boolValue
     *
     * @return bool
     */
    public function reverseTransform($boolValue)
    {
        return (int) $boolValue;
    }
}
