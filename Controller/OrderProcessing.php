<?php

namespace Bleumi\PaymentAggregator\Controller;

use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Http\Client\Curl;
use Magento\Sales\Model\Order;

class OrderProcessing
{
    protected $orderFactory;
    protected $logger;
    protected $scopeConfig;
    protected $apiKey;
    
    /**
     * Constructor.
     *
     * @param \Magento\Sales\Model\OrderFactory                  $orderFactory      Order Factory.
     * @param \Psr\Log\LoggerInterface                           $logger            Log writer.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig       Scope Config.
     * @param \Magento\Framework\Http\Client\Curl                $curl              Curl Client
     *
     * @return void
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Curl $curl
    ) {
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->apiKey = $this->scopeConfig->getValue('payment/bleumimethod/api_key', ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * Update order details
     * 
     * @param string $order_id
     *
     * @return string
     */
    public function updateOrder($order_id)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($order_id);
        if (!$order->getId()) {
            $this->logger->info('Missing order: ' . $order_id);
            return 'no-order';
        }
        
        try {
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("X-Api-Key", $this->apiKey);
            $this->curl->get("https://api.bleumi.io/v1/payment/" . $order->getId());
            
            $body = $this->curl->getBody();
            $this->logger->info($body);

            $response = json_decode($body, true);
            if(!empty($response['record'])) {
                $amt_due = floatval($response['record']['amt_due']);
                $amt_recv_pending = floatval($response['record']['amt_recv_online_pending']);

                if ($amt_recv_pending > 0) {
                        $order->setState("awaiting_confirmation", true)->save();
                        $order->setStatus("awaiting_confirmation", true)->save();
                } else {
                    if ($amt_due > 0) {
                        if ($response['record']['amt_due'] === $response['record']['total']) {
                            $this->logger->info(filter_input(INPUT_GET, 'user_force'));
                            if (filter_input(INPUT_GET, 'user_force') === 'yes') {
                                $this->logger->info('user marked as paid');
                                $order->addStatusHistoryComment('User marked as paid, payment not verified by Bleumi')->save();
                                return;
                            }

                            return 'no-payment';
                        } else {
                            $order->setState("partial_payment", true)->save();
                            $order->setStatus("partial_payment", true)->save();
                        }
                    } elseif ($amt_due < 0) {
                        $order->setState("over_payment", true)->save();
                        $order->setStatus("over_payment", true)->save();
                    } else {
                        $next_status = $this->scopeConfig->getValue('payment/bleumimethod/next_status', ScopeInterface::SCOPE_STORE);

                        if (isset($next_status)) {
                            $order->setState($next_status, true)->save();
                            $order->setStatus($next_status, true)->save();
                        } else {
                            $order->setState(Order::STATE_PROCESSING, true)->save();
                            $order->setStatus(Order::STATE_PROCESSING, true)->save();
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            $this->logger->critical('Bleumi payment validation failed ' . $order_id . ' order_id', ["exception" => $th]);
        }
        
        return 'done';
    }
}
