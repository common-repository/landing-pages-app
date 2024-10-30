<?php
namespace LandingPages\Wordpress\Plugin\LandingPagesPlugin\Service\ApiClient;

class LandingPagesApiErrorException extends \Exception
{
    public function __construct()
    {
        parent::__construct('We cannot establish a connection to the API.');
    }
}
