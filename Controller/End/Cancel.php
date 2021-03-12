<?php

namespace Bleumi\PaymentAggregator\Controller\End;

use Magento\Framework\App\Action\Action;

class Cancel extends Action
{

    /**
     * Get Checkout
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }


    /**
     * Redirect to to checkout success
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_getCheckout()->getLastRealOrderId()) {
            $order = $this->_getCheckout()->getLastRealOrder();
            if ($order->getId() && !$order->isCanceled()) {
                $order->registerCancellation('Canceled by Customer')->save();
            }

            $this->_getCheckout()->restoreQuote();
            $this->_redirect('checkout/cart');
        }
    }
}
