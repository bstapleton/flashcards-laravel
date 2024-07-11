<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class BaseTransformer extends TransformerAbstract
{
    public function transform($object)
    {
        return $object;
    }
}
