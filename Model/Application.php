<?php

namespace Expressly\Expressly\Model;

use Expressly\Client;

class Application
{
    private $app;

    public function __construct(MerchantProvider $merchantProvider)
    {
        $client = new Client('magento2');

        $app = $client->getApp();

        $app['merchant.provider'] = $merchantProvider;

        $this->app = $app;
    }

    public function getApp()
    {
        return $this->app;
    }
}