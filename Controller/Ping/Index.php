<?php

namespace Expressly\Expressly\Controller\Ping;

use Expressly\Expressly\Model\MerchantProvider;
use Expressly\Expressly\Model\ResourceModel\Merchant\Collection;
use Expressly\Presenter\PingPresenter;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    public function execute()
    {
        $presenter = new PingPresenter();

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($presenter->toArray());

        return $result;
    }
}