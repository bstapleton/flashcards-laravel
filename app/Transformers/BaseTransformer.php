<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

/**
 * No-op transformer, returns passed object as-is, but hooks the response macro to standardise the response structure.
 */
class BaseTransformer extends TransformerAbstract
{
    public function transform($object)
    {
        return $object;
    }
}
