<?php

namespace Expressly\Expressly\Model;

use Expressly\Client;

class Application
{
    private $app;

    public function __construct(MerchantProvider $merchantProvider)
    {
        $client = new Client('magento2', [
            'external' => [
                'hosts' => [
                    'default' => 'localhost:8080/api/v2',
                    'admin' => 'localhost:8080/api/admin'
                ]
            ]
        ]);

        $app = $client->getApp();

        $app['merchant.provider'] = $merchantProvider;

        $this->app = $app;
    }

    public function getApp()
    {
        return $this->app;
    }
}