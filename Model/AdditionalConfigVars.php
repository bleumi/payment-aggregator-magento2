<?php

namespace Bleumi\PaymentAggregator\Model;

use \Magento\Checkout\Model\ConfigProviderInterface;

class AdditionalConfigVars implements ConfigProviderInterface
{
    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig       Scope Configuration.
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

	public function getConfig()
	{
		$additionalVariables['bleumi'] = array(
			'payment_options' => $this->scopeConfig->getValue('payment/bleumimethod/payment_options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
		);

		return $additionalVariables;
	}
}
