<?php

namespace Bleumi\PaymentAggregator\Model;

class BleumiMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = "bleumimethod";
    protected $_isOffline = true;

    /**
     * Check if Bleumi API key is set
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote = null Parameter description.
     *
     * @return object
     */
    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        $apiKey = $this->_scopeConfig->getValue(
            'payment/bleumimethod/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        if (!$apiKey) {
            return false;
        }
        
        return parent::isAvailable($quote);
    }
}
