<?php

namespace Expressly\Expressly\Model;

use Expressly\Entity\Merchant;
use Expressly\Expressly\Model\ResourceModel\Merchant as MerchantResourceModel;
use Expressly\Provider\MerchantProviderInterface;
use Magento\Framework\Model\AbstractModel;

class MerchantProvider extends AbstractModel implements MerchantProviderInterface
{
    const COLUMN_API_KEY = 'api_key';
    const COLUMN_HOST = 'host';
    const COLUMN_PATH = 'path';

    protected function _construct()
    {
        $this->_init(MerchantResourceModel::class);
    }

    public function setMerchant(Merchant $merchant)
    {
        $this->setData([
            self::COLUMN_API_KEY => $merchant->getApiKey(),
            self::COLUMN_HOST => $merchant->getHost(),
            self::COLUMN_PATH => $merchant->getPath()
        ]);
    }

    public function getMerchant()
    {
        $merchant = new Merchant();
        $merchant
            ->setApiKey($this->getData(self::COLUMN_API_KEY))
            ->setHost($this->getData(self::COLUMN_HOST))
            ->setPath($this->getData(self::COLUMN_PATH));

        return $merchant;
    }
}