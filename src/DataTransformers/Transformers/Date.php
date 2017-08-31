<?php

namespace BadChoice\Reports\DataTransformers\Transformers;

use BadChoice\Reports\DataTransformers\TransformsValueInterface;

class Date implements TransformsValueInterface
{
    public function transform($value){
        return substr(timeZoned($value),0,10);
    }
}