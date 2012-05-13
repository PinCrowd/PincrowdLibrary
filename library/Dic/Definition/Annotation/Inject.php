<?php

namespace Pincrowd\Di\Definition\Annotation;

use Pincrowd\Code\Annotation\Annotation;

class Inject implements Annotation
{

    protected $content = null;

    public function initialize($content)
    {
        $this->content = $content;
    }
}