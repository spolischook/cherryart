<?php

namespace Cherry\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class PictureTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return ['fileName' => $value, 'file' => null];
    }

    public function reverseTransform($value)
    {
        return $value['fileName'];
    }
}
