<?php

namespace Expressly\Expressly\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Dashboard extends Container
{
    protected function _construct()
    {
        $this->_controller = 'expressly_dashboard';
        $this->_blockGroup = 'Expressly_Expressly';
        $this->_headerText = __('Expressly Dashboard');

        parent::_construct();
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}