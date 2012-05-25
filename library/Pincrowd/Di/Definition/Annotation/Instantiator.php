<?php

namespace Pincrowd\Di\Definition\Annotation;

use Pincrowd\Code\Annotation\Annotation;

class Instantiator implements Annotation
{

    protected $content = null;

    public function initialize($content)
    {
        $this->content = $content;
    }
}