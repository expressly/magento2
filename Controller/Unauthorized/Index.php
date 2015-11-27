<?php

namespace Expressly\Expressly\Controller\Unauthorized;

use Expressly\Presenter\RegisteredPresenter;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setHttpResponseCode(401);

        return $result;
    }
}