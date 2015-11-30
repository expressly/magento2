<?php

namespace Expressly\Expressly\Model\ResourceModel\Merchant;

use Expressly\Expressly\Model\MerchantProvider;
use Expressly\Expressly\Model\ResourceModel\Merchant;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(MerchantProvider::class, Merchant::class);
    }
}