<?php

namespace Expressly\Expressly\Controller\Batch;

use Expressly\Presenter\BatchInvoicePresenter;
use Expressly\Presenter\PingPresenter;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Invoice extends Action
{
    /**
     * Invoice constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    public function getOrderCollectionFactory()
    {
        return $this->_orderCollectionFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws GenericException
     */
    public function execute()
    {
        $result   = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $invoices = [];

        try {
            $json = file_get_contents('php://input');
            $json = json_decode($json);

            if (!property_exists($json, 'customers')) {
                throw new GenericException('Invalid JSON input');
            }

            foreach ($json->customers as $customer) {

                if (!property_exists($customer, 'email')) {
                    continue;
                }

                $mageOrders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                    '*'
                )->addFieldToFilter(
                    'customer_email',
                    $customer->email
                )->setOrder(
                    'created_at',
                    'desc'
                );

                $invoice = new \Expressly\Entity\Invoice();
                $invoice->setEmail($customer->email);

                foreach ($mageOrders as $mageOrder) {
                    $total = $mageOrder->getData('base_grand_total');
                    $tax = $mageOrder->getData('base_tax_amount');
                    $order = new \Expressly\Entity\Order();
                    $order
                        ->setId($mageOrder->getData('increment_id'))
                        ->setDate(new \DateTime($mageOrder->getData('created_at')))
                        ->setCurrency($mageOrder->getData('base_currency_code'))
                        ->setTotal((double)$total - (double)$tax, (double)$tax)
                        ->setItemCount((int)$mageOrder->getData('total_qty_ordered'))
                        ->setCoupon($mageOrder->getData('coupon_code'));
                    $invoice->addOrder($order);
                }

                $invoices[] = $invoice;
            }

            $presenter = new BatchInvoicePresenter($invoices);
            $result->setData($presenter->toArray());
        } catch (\Exception $e) {
            // log error
            $result->setData(['error'=>$e->getMessage()]);
        }

        return $result;
    }
}