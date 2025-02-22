<?php
namespace LandingPages\Wordpress\Plugin\LandingPagesPlugin\Service\ApiClient;

class InvalidTokenException extends \Exception
{
    public function __construct()
    {
        parent::__construct('You need to provide a proper API token, and have at least one published Landing Page on your account.');
    }
}
