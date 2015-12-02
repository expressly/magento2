<?php

namespace Expressly\Expressly\Model;

use Expressly\Entity\Merchant;
use Expressly\Expressly\Model\ResourceModel\Merchant as MerchantResourceModel;
use Expressly\Provider\MerchantProviderInterface;

class MerchantProvider implements MerchantProviderInterface
{
    const COLUMN_API_KEY = 'api_key';
    const COLUMN_HOST = 'host';
    const COLUMN_PATH = 'path';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->_config = $config;
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
            ->setApiKey($this->_config->getValue('expressly/consumer/'.self::COLUMN_API_KEY))
            ->setHost($this->_config->getValue('expressly/consumer/'.self::COLUMN_HOST))
            ->setPath($this->_config->getValue('expressly/consumer/'.self::COLUMN_PATH));

        return $merchant;
    }
}