<?php

namespace Expressly\Expressly\Controller\Test;

use Magento\Framework\App\Action\Action;

class Test extends Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}