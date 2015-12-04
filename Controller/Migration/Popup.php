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

    /**
     * Popup constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param StoreManagerInterface $storeManager
     * @param ForwardFactory $resultForwardFactory
     * @param Application $application
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        StoreManagerInterface $storeManager,
        ForwardFactory $resultForwardFactory,
        Application $application
    ) {
        parent::__construct($context);
        $this->pageFactory          = $pageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->application          = $application;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Forward|void
     */
    public function execute()
    {

        $app = $this->application->getApp();

        $merchant = $app['merchant.provider']->getMerchant();

        $uuid = $this->_request->getParam('uuid');
        $event = new CustomerMigrateEvent($merchant, $uuid);
        $content = '';
        try {
            $app['dispatcher']->dispatch(CustomerMigrationSubscriber::CUSTOMER_MIGRATE_POPUP, $event);
            if (!$event->isSuccessful()) {
                throw new GenericException(Router::processError($event));
            }
            $content = $event->getContent();
        } catch (\Exception $e) {
            // log error
            var_dump($e->getMessage());
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

        // XML injection doesn't work to add this javascript as we're overriding the page completely
        $js = '<script type="text/javascript">
                    (function() {
                        popupContinue = function (event) {
                            event.style.display = \'none\';
                            var loader = event.nextElementSibling;
                            loader.style.display = \'block\';
                            loader.nextElementSibling.style.display = \'none\';
                            window.location.replace(window.location.origin + window.location.pathname + \'/migrate\');
                        };
                        popupClose = function (event) {
                            window.location.replace(window.location.origin);
                        };
                        openTerms = function (event) {
                            window.open(event.href, \'_blank\');
                        };
                        openPrivacy = function (event) {
                            window.open(event.href, \'_blank\');
                        };
                        (function () {
                            // make sure our popup is on top or hierarchy
                            content = document.getElementById(\'xly\');
                            document.body.insertBefore(content, document.body.children[0]);
                        })();
                    })();
                </script>';


        $page->renderResult($this->getResponse());

        $this->getResponse()->appendBody($js);
        $this->getResponse()->appendBody($content);

        return;
    }
}