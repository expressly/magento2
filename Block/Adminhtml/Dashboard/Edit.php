<?php

namespace Expressly\Expressly\Block\Adminhtml\Dashboard;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

class Edit extends Container
{
    protected $_coreRegistry;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
    }

    protected function _construct()
    {
        $this->_objectId = 'api_key';
        $this->_blockGroup = 'Expressly_Expressly';
        $this->_controller = 'expressly_dashboard';

        parent::_construct();
    }

    public function getHeaderText()
    {
        return __('Header text');
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'expresslyadmin/dashboard',
            [
                'current' => true,
                'back' => 'edit',
                'active_tab' => ''
            ]
        );
    }
}