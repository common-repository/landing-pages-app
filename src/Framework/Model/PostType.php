<?php
namespace LandingPages\Wordpress\Plugin\Framework\Model;

abstract class PostType
{
    protected $parameters = [];

    public function getParameters()
    {
        return $this->parameters;
    }
}
