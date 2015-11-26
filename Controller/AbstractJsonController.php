<?php

namespace Expressly\Expressly\Controller;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

abstract class AbstractJsonController extends AbstractAction
{
    protected $jsonFactory;

    public function __construct(Context $context, JsonFactory $jsonFactory)
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
    }

    abstract function execute();
}