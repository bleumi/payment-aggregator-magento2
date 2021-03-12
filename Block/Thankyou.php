<?php

namespace Bleumi\PaymentAggregator\Block;

class Thankyou extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * Get Order
     *
     * @return string
     */
    public function getOrder()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        return $order;
    }
}
