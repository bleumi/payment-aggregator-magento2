<?php

namespace Bleumi\PaymentAggregator\Controller;

use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order;

class Webhook
{
    protected $logger;
    protected $orderProc;
    
    /**
     * Constructor.
     *
     * @param \Psr\Log\LoggerInterface                                $logger            Log writer.
     * @param \Bleumi\PaymentAggregator\Controller\OrderProcessing    $orderProc         OrderProcessing
     *
     * @return void
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Bleumi\PaymentAggregator\Controller\OrderProcessing $orderProc
    ) {
        $this->logger = $logger;
        $this->orderProc = $orderProc;
    }
    
    /**
     * Process Webhook
     *
     * @return string
     */
    public function postWebhook()
    {
        try {
            $body = file_get_contents("php://input");
            $this->logger->info($body);
            
            $request = json_decode($body, true);
            
            $order_id = intval($request['id']);
            $this->orderProc->updateOrder($order_id);
        } catch (\Throwable $th) {
            $this->logger->critical('Bleumi payment validation failed ' . filter_input(INPUT_GET, 'order_id'), ["exception" => $th]);
        }
    }
}
