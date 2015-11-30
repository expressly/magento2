<?php

namespace Expressly\Expressly\Controller;

use Expressly\Entity\Route;
use Expressly\Event\ResponseEvent;
use Expressly\Expressly\Model\Application;
use Expressly\Route\BatchCustomer;
use Expressly\Route\BatchInvoice;
use Expressly\Route\CampaignMigration;
use Expressly\Route\CampaignPopup;
use Expressly\Route\Ping;
use Expressly\Route\Registered;
use Expressly\Route\UserData;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;

class Router implements RouterInterface
{
    protected $actionFactory;
    protected $response;
    protected $application;

    const MODULE_NAME = 'expressly';

    public function __construct(ActionFactory $actionFactory, ResponseInterface $response, Application $application)
    {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
        $this->application = $application->getApp();
    }

    public function match(RequestInterface $request)
    {
        if (($request->getModuleName() == null) && method_exists($request, 'getPathInfo')) {
            $route = $this->application['route.resolver']->process($request->getPathInfo());

            if ($route instanceof Route) {
                switch ($route->getName()) {
                    case Ping::getName():
                        return $this->dispatch($request, 'ping');
                        break;
                    case Registered::getName():
                        return $this->dispatch($request, 'registered');
                        break;
                    case UserData::getName():
                        $data = $route->getData();

                        return $this->dispatch($request, 'user', 'index', $data);
                        break;
                    case CampaignPopup::getName():
                        $data = $route->getData();

                        return $this->dispatch($request, 'migration', 'popup', $data);
                        break;
                    case CampaignMigration::getName():
                        $data = $route->getData();

                        return $this->dispatch($request, 'migration', 'migrate', $data);
                        break;
                    case BatchCustomer::getName():
                        return $this->dispatch($request, 'batch', 'customer');
                        break;
                    case BatchInvoice::getName():
                        return $this->dispatch($request, 'batch', 'invoice');
                        break;
                }
            }

            if (http_response_code() === 401) {
                return $this->dispatch($request, 'unauthorized');
            }
        }

//        return $this->actionFactory->create(Forward::class, ['request' => $request]);
    }

    private function dispatch($request, $controller, $action = 'index', $parameters = array())
    {
        $request
            ->setModuleName('expressly')
            ->setControllerName($controller)
            ->setActionName($action)
            ->setParams($parameters);

        $request->setDispatched(true);

        return $this->actionFactory->create(Forward::class, ['request' => $request]);
    }


    public static function processError(ResponseEvent $event)
    {
        $content = $event->getContent();
        $message = array(
            $content['description']
        );

        $addBulletPoints = function ($data, $header) use (&$message) {
            $message[] = $header;
            foreach ($data as $point) {
                $message[] = $point;
            }
        };

        $addBulletPoints($content['causes'], 'Possible Causes:');
        $addBulletPoints($content['actions'], 'Possible Actions:');

        return implode(',', $message);
    }
}
