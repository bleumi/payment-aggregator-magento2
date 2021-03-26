<?php

namespace Bleumi\PaymentAggregator\Controller\Start;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Http\Client\Curl;
use Magento\Sales\Model\Order;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;
    protected $resultJsonFactory;
    protected $logger;
    protected $scopeConfig;
    protected $curl;
    protected $apiKey;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\Action\Context              $context           Context.
     * @param \Magento\Checkout\Model\Session                    $checkoutSession   Session.
     * @param \Magento\Framework\Controller\Result\JsonFactory   $resultJsonFactory Result JSON Factory.
     * @param \Psr\Log\LoggerInterface                           $logger            Log write.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig       Scope Configuration.
     * @param \Magento\Framework\Http\Client\Curl                $curl              Curl Client
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Curl $curl
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->apiKey = $this->scopeConfig->getValue('payment/bleumimethod/api_key', ScopeInterface::SCOPE_STORE);

        parent::__construct($context);
    }
    
    /**
     * Get store base URL
     *
     * @return object
     */
    private function getBaseUrl()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        return $storeManager->getStore()->getBaseUrl();
    }

    /**
     * Start checkout by requesting checkout code and dispatching customer to Bleumi.

     * @return object
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();

        try {
            $name = '';
            if(!empty($order->getCustomerFirstname())) {
                $name = $name . $order->getCustomerFirstname();
            }
            if(!empty($order->getCustomerLastname())) {
                $name = $name . ' ' . $order->getCustomerLastname();
            }

            $billing = $order->getBillingAddress();

            $params = array(
                "id" => $order->getId(),
                "currency" => $order->getOrderCurrencyCode(),
                "invoice_date" => intval(date("Ymd")),
                "metadata" => array (
                    "no_invoice" => true
                ),
                "success_url" => $this->_url->getUrl("bleumi/end/redirect", ['_query' => ["order_id" => $order->getId()]]),
                "cancel_url" => $this->_url->getUrl("bleumi/end/cancel"),
                "notify_url" => $this->getBaseUrl() . 'rest/V1/bleumi/webhook',
                "record" => array (
                    "client_info" => array (
                        "type" => "individual",
                        "name" => $name,
                        "email" => $order->getCustomerEmail()
                    ),
                    "line_item" => array( array(
                        "name" => "Order",
                        "description" => "#" . $order->getId(),
                        "quantity" => 1,
                        "rate" => $order->getGrandTotal()
                    ))
                )
            );

            if (!empty($billing->getCountryId())) {
                $params["record"]["client_info"]["country"] = $billing->getCountryId();
            }

            $this->logger->info(json_encode($params));

            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("X-Api-Key", $this->apiKey);
            $this->curl->post("https://api.bleumi.io/v1/payment", json_encode($params));

            $body = $this->curl->getBody();
            $this->logger->info($body);

            $response = json_decode($body, true);

            if (!empty($response['payment_url'])) {
                $order->setState(Order::STATE_PENDING_PAYMENT, true);
                $order->setStatus(Order::STATE_PENDING_PAYMENT, true);


                $url_parts = parse_url($response['payment_url']);

                $params = array();
                parse_str($url_parts['query'], $params);
                $params['no_redirect'] = 'yes';
                $url_parts['query'] = http_build_query($params);


                $url = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'] . '?' . $url_parts['query'];


                $order->addStatusHistoryComment('Bleumi Payment URL: ' . $url);


                $order->save();

                $result = $this->resultJsonFactory->create();
                return $result->setData(['redirectUrl' => $response['payment_url']]);
            }
        } catch (\Throwable $th) {
            $this->logger->critical('Bleumi POST', ["exception" => $th]);
        }
        
        $order->registerCancellation('Canceled due to errors')->save();
        
        $this->checkoutSession->restoreQuote();
        
        throw new LocalizedException(__('Something went wrong while receiving API Response'));
    }
}
