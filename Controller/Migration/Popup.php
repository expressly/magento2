<?php

namespace Expressly\Expressly\Controller\Migration;

use Expressly\Event\CustomerMigrateEvent;
use Expressly\Exception\GenericException;
use Expressly\Expressly\Controller\Router;
use Expressly\Expressly\Model\Application;
use Expressly\Expressly\Model\ResourceModel\Merchant\Collection;
use Expressly\Presenter\PingPresenter;
use Expressly\Subscriber\CustomerMigrationSubscriber;
use Magento\Cms\Helper\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class Popup extends Action
{
    private $pageFactory;
    private $merchant;
    private $application;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        StoreManagerInterface $storeManager,
        ForwardFactory $resultForwardFactory,
        Collection $merchantCollection,
        Application $application
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->merchant = $merchantCollection->getItemById($storeManager->getWebsite()->getId())->getMerchant();
        $this->application = $application;
    }

    public function execute()
    {
        $uuid = $this->_request->getParam('uuid');
        $event = new CustomerMigrateEvent($this->merchant, $uuid);

        try {
            $this->application['dispatcher']->dispatch(CustomerMigrationSubscriber::CUSTOMER_MIGRATE_POPUP, $event);

            if ($event->isSuccessful()) {
                throw new GenericException(Router::processError($event));
            }

            var_dump($event->getContent());
        } catch (\Exception $e) {
            // log error
        }

        $pageId = $this->_objectManager
            ->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue(
                \Magento\Cms\Helper\Page::XML_PATH_HOME_PAGE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        $page = $this->_objectManager->get(Page::class)->prepareResultPage($this, $pageId);

        if (!$page) {
            $forward = $this->resultForwardFactory->create();
            $forward->forward('defaultIndex');

            return $forward;
        }

        return $page;
    }
}