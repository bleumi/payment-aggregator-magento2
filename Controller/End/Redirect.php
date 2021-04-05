<?php

namespace Bleumi\PaymentAggregator\Controller\End;

use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Http\Client\Curl;
use Magento\Sales\Model\Order;

class Redirect extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $logger;
    protected $orderProc;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\Action\Context                   $context           Context.                                                          
     * @param \Magento\Framework\View\Result\PageFactory              $resultPageFactory Result Page Factory.
     * @param \Psr\Log\LoggerInterface                                $logger            Log writer.
     * @param \Bleumi\PaymentAggregator\Controller\OrderProcessing    $orderProc         OrderProcessing
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger,
        \Bleumi\PaymentAggregator\Controller\OrderProcessing $orderProc
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->orderProc = $orderProc;
        
        parent::__construct($context);
    }

    /**
     * Get Checkout
     *
     * @return \Magento\Checkout\Model\Session      Session.
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
        $order_id = $this->_getCheckout()->getLastRealOrderId();

        if (!empty($order_id)) {
            $response = $this->orderProc->updateOrder($order_id);
            
            if($response === 'no-payment') {
                return $this->_redirect('checkout/cart');
            }
        }

        $this->_redirect('checkout/onepage/success', ['_secure' => true]);
    }
}
