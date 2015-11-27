<?php

namespace Expressly\Expressly\Controller\User;

use Expressly\Entity\Address;
use Expressly\Entity\Customer;
use Expressly\Entity\Email;
use Expressly\Entity\Phone;
use Expressly\Expressly\Model\ResourceModel\Merchant\Collection as MerchantCollection;
use Expressly\Presenter\CustomerMigratePresenter;
use Expressly\Presenter\PingPresenter;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Action
{
    private $merchant;
    private $customerRegistry;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        MerchantCollection $merchantCollection,
        CustomerRegistry $customerRegistry
    ) {
        parent::__construct($context);
        $website = $storeManager->getWebsite();
        $this->merchant = $merchantCollection->getItemById($website->getId())->getMerchant();
        $this->customerRegistry = $customerRegistry;
    }

    public function execute()
    {
        $emailAddress = $this->_request->getParam('email');
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $magentoCustomer = $this->customerRegistry->retrieveByEmail($emailAddress);

            if (!$magentoCustomer->isEmpty()) {
                $customer = new Customer();
                $customer
                    ->setFirstName($magentoCustomer->getFirstname())
                    ->setLastName($magentoCustomer->getLastname());

                $email = new Email();
                $email
                    ->setAlias('default')
                    ->setEmail($emailAddress);
                $customer->addEmail($email);

                $defaultBilling = $magentoCustomer->getDefaultBillingAddress();
                $defaultShipping = $magentoCustomer->getDefaultShippingAddress();

                foreach ($magentoCustomer->getAddresses() as $magentoAddress) {
                    $address = new Address();
                    $address
                        ->setFirstName($magentoAddress->getFirstname())
                        ->setLastName($magentoAddress->getLastname())
                        ->setCompanyName($magentoAddress->getCompany())
                        ->setAddress1($magentoAddress->getStreetLine(1))
                        ->setAddress2($magentoAddress->getStreetLine(2))
                        ->setCity($magentoAddress->getCity())
                        ->setStateProvince($magentoAddress->getRegion())
                        ->setZip($magentoAddress->getPostcode())
                        ->setCountry($magentoAddress->getCountry());

                    $phone = new Phone();

                    $customer->addPhone($phone);
                    $address->setPhonePosition($customer->getPhoneIndex($phone));

                    $primary = false;
                    $type = null;
                    if ($defaultBilling->getId() == $magentoAddress->getId()) {
                        $primary = true;
                        $type = Address::ADDRESS_BILLING;
                    }
                    if ($defaultShipping->getId() == $magentoAddress->getId()) {
                        $primary = true;
                        $type = ($type == Address::ADDRESS_BILLING) ? Address::ADDRESS_BOTH : Address::ADDRESS_SHIPPING;
                    }

                    $customer->addAddress($address, $primary, $type);
                }

                $presenter = new CustomerMigratePresenter($this->merchant, $customer, $emailAddress, 55);

                $result->setData($presenter->toArray());
            }
        } catch (\Exception $e) {
            // log error
            $result->setData([]);
        }

        return $result;
    }
}