<?php
namespace LandingPages\Wordpress\Plugin\Framework\Event;

use LandingPages\Wordpress\Plugin\Framework\Kernel\ContainerCollection;

abstract class AbstractEvent
{
    protected $containerCollection;
    protected $filterArguments;

    public function __construct(ContainerCollection $containerCollection)
    {
        $this->containerCollection = $containerCollection;
    }

    public abstract function filter();
}
