<?php

namespace Expressly\Expressly\Controller\Batch;

use Expressly\Exception\GenericException;
use Expressly\Presenter\BatchCustomerPresenter;
use Expressly\Presenter\PingPresenter;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Customer extends Action
{
    private $customerRegistry;

    public function __construct(Context $context, CustomerRegistry $customerRegistry)
    {
        parent::__construct($context);
        $this->customerRegistry = $customerRegistry;
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $json = file_get_contents('php://input');
            $json = json_decode($json);
            $existingCustomers = [];
            $pendingCustomers = [];

            if (!property_exists($json, 'emails')) {
                throw new GenericException('Invalid JSON input');
            }

            foreach ($json->emails as $email) {
                try {
                    $magentoCustomer = $this->customerRegistry->retrieveByEmail($email);

                    if (!$magentoCustomer->isEmpty()) {
                        $magentoCustomer->getDataByKey('is_active') ?
                            $existingCustomers[] = $email : $pendingCustomers[] = $email;
                    }
                } catch (NoSuchEntityException $e) {
                    // Do nothing b/c Magento doesn't provide "does exist" functionality, and dies abruptly
                }
            }

            $presenter = new BatchCustomerPresenter($existingCustomers, [], $pendingCustomers);
            $result->setData($presenter->toArray());
        } catch (\Exception $e) {
            // log error
            $result->setData([]);
        }

        return $result;
    }
}