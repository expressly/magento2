<?php

namespace Expressly\Expressly\Controller\Migration;

use Expressly\Event\CustomerMigrateEvent;
use Expressly\Event\ResponseEvent;
use Expressly\Exception\GenericException;
use Expressly\Exception\UserExistsException;
use Expressly\Expressly\Controller\Router;
use Expressly\Expressly\Model\Application;
use Expressly\Expressly\Model\ResourceModel\Merchant\Collection;
use Expressly\Presenter\PingPresenter;
use Expressly\Subscriber\CustomerMigrationSubscriber;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Migrate extends Action
{
    private $customerFactory;
    private $addressFactory;
    private $customerSession;
    private $productRepository;
    private $cart;
    private $application;
    private $countryProvider;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        Cart $cart,
        Collection $merchantCollection,
        Application $application
    ) {
        parent::__construct($context);
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->merchant = $merchantCollection->getItemById($storeManager->getWebsite()->getId())->getMerchant();
        $this->application = $application->getApp();
        $this->countryProvider = $this->application['country_code.provider'];
    }

    public function execute()
    {
        $uuid = $this->_request->getParam('uuid');
        $exists = false;

        try {
            $event = new CustomerMigrateEvent($this->merchant, $uuid);
            $this->application['dispatcher']->dispatch(CustomerMigrationSubscriber::CUSTOMER_MIGRATE_DATA, $event);

            $content = $event->getContent();
            if (!$event->isSuccessful()) {
                if (!empty($json['code']) && $json['code'] == 'USER_ALREADY_MIGRATED') {
                    $exists = true;

                    throw new UserExistsException();
                }

                throw new GenericException(Router::processError($event));
            }

            // if customer exists
            $email = $content['migration']['data']['email'];
            if (true == false) {
                $exists = true;
                $event = new CustomerMigrateEvent($this->merchant, $uuid, CustomerMigrateEvent::EXISTING_CUSTOMER);
            } else {
                $customer = $content['migration']['data']['customerData'];

                $magentoCustomer = $this->customerFactory->create();

                $magentoCustomer
                    ->setEmail($email)
                    ->setFirstname()
                    ->setLastname()
                    ->setPassword(md5('xly' . microtime()))
                    ->save();

                foreach ($customer['addresses'] as $index => $address) {
                    $magentoAddress = $this->addressFactory->create();

                    $safelyGet = function ($key) use ($address) {
                        if (!empty($address[$key])) {
                            return $address[$key];
                        }

                        return '';
                    };

                    $magentoAddress
                        ->setCustomer($magentoCustomer)
                        ->setFirstname($address['firstName'])
                        ->setLastname($address['lastName'])
                        ->setCompanyName($safelyGet('company'))
                        ->setStreet([
                            $safelyGet('address1'),
                            $safelyGet('address2')
                        ])
                        ->setCity($safelyGet('city'))
                        ->setRegion($safelyGet('stateProvince'))
                        ->setPostcode($safelyGet('zip'))
                        ->setCountry($this->countryProvider->getIso2($address['country']))
                        ->save();

                    if ($customer['billingAddress'] == $index) {
                        $magentoCustomer->setDefaultBilling($magentoAddress->getId());
                    }
                    if ($customer['shippingAddress'] == $index) {
                        $magentoCustomer->setDefaultShipping($magentoAddress->getId());
                    }
                }

                $magentoCustomer->sendPasswordResetConfirmationEmail();

                // log user in
                $this->customerSession->loginById($magentoCustomer->getId());
            }

            // add item to cart
            if (!empty($content['cart']['productId'])) {
                $this->cart->addProduct($content['cart']['productId']);
                $this->cart->save();
            }

            // add coupon to cart
            if (!empty($content['cart']['couponCode'])) {
                $this->cart->getQuote()->setCouponCode($content['cart']['couponCode']);
                $this->cart->saveQuote();
            }

            $this->application['dispatcher']->dispatch(CustomerMigrationSubscriber::CUSTOMER_MIGRATE_SUCCESS, $event);
        } catch (GenericException $e) {
            // log error
        }

        if ($exists) {
            // show exists popup
        }
    }
}