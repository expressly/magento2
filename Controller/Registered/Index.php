<?php

namespace Expressly\Expressly\Controller\Registered;

use Expressly\Presenter\RegisteredPresenter;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    public function execute()
    {
        $presenter = new RegisteredPresenter();

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData($presenter->toArray());

        return $result;
    }
}