<?php

namespace App\Transformers;

use App\Models\Tag;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
{
    public function transform(Tag $tag): array
    {
        return [
            'name' => $tag->name,
            'colour' => strtolower($tag->colour->name),
        ];
    }
}
