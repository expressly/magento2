<?php

/**
 * @author Expressly Team <info@buyexpressly.com>
 */
namespace Expressly\Expressly\Model\Config\Backend;

use Expressly\Event\MerchantEvent;
use Expressly\Event\PasswordedEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Exception\InvalidAPIKeyException;
use Expressly\Subscriber\MerchantSubscriber;

/**
 * @package Expressly\Expressly\Model\Config\Backend
 */
class Register extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Expressly\Expressly\Model\Application
     */
    protected $_application;

    /**
     * Register constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Expressly\Expressly\Model\Application $application,
        array $data = []
    ) {
        $this->_messageManager = $messageManager;
        $this->_application    = $application;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return \Magento\Framework\Message\ManagerInterface
     */
    public function getMessageManager()
    {
        return $this->_messageManager;
    }

    /**
     * @return \Expressly\Expressly\Model\Application
     */
    public function getApplication()
    {
        return $this->_application;
    }

    /**
     * @return void
     */
    public function afterSave()
    {
        $app = $this->getApplication()->getApp();

        $provider   = $app['merchant.provider'];
        $dispatcher = $app['dispatcher'];

        $merchant = $provider->getMerchant();
        $event = new PasswordedEvent($merchant);
        try {
            $dispatcher->dispatch(MerchantSubscriber::MERCHANT_REGISTER, $event);
            if (!$event->isSuccessful()) {
                throw new InvalidAPIKeyException();
            }
            $this->getMessageManager()->addSuccess('MERCHANT_REGISTER');
        } catch (\Exception $e) {
            $app['logger']->error(ExceptionFormatter::format($e));
            $this->getMessageManager()->addError(__('Your values could not be transmitted to the server. Please try resubmitting, or contacting info@buyexpressly.com'));
        }
    }
}
