<?php
/**
 */

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderCancelAfter implements ObserverInterface
{

    private $_couponUsage;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $_ruleCustomerFactory;

     /**
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Model\Rule\CustomerFactory $ruleCustomerFactory
     * @param \Magento\SalesRule\Model\Coupon $coupon
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\Usage $couponUsage
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Coupon\Usage $couponUsage,
        \Magento\SalesRule\Model\Rule\CustomerFactory $ruleCustomerFactory
    ) {
        $this->_couponUsage = $couponUsage;
        $this->_ruleCustomerFactory = $ruleCustomerFactory;
    }


    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $couponCode = $order->getCouponCode();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $couponObject = $objectManager->create ('Magento\SalesRule\Model\Coupon');
        $couponObject->loadByCode($couponCode);
        $couponId = $couponObject->getCouponId();
        $customerId = $order->getCustomerId();
        if ($customerId) {
            $this->_couponUsage->decreaseCustomerCouponTimesUsed($customerId, $couponId);
        }

        /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
        $ruleCustomer = $this->_ruleCustomerFactory->create();
        $ruleCustomer->loadByCustomerRule($customerId, $couponObject->getRuleId());

        if ($ruleCustomer->getId()) {
            $ruleCustomer->setTimesUsed(max($ruleCustomer->getTimesUsed() - 1, 0));
        } 
        $ruleCustomer->save();
    }
}
