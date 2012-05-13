<?php
namespace Pincrowd\Di;

interface ServiceLocation extends Locator
{
    public function set($name, $service);
}
