<?php

namespace Bleumi\PaymentAggregator\Setup;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

class InstallData implements InstallDataInterface
{
    const PARTIAL_PAYMENT_CODE = 'partial_payment';
    const PARTIAL_PAYMENT_LABEL = 'Partially Paid';

    const OVER_PAYMENT_CODE = 'over_payment';
    const OVER_PAYMENT_LABEL = 'Over Paid';

    const AWAITING_CONFIRMATION_CODE = 'awaiting_confirmation';
    const AWAITING_CONFIRMATION_LABEL = 'Awaiting Payment Confirmation';

    protected $statusFactory;
    protected $statusResourceFactory;

    public function __construct(
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->addOrderStatus(self::PARTIAL_PAYMENT_CODE, self::PARTIAL_PAYMENT_LABEL);
        $this->addOrderStatus(self::OVER_PAYMENT_CODE, self::OVER_PAYMENT_LABEL);
        $this->addOrderStatus(self::AWAITING_CONFIRMATION_CODE, self::AWAITING_CONFIRMATION_LABEL);
        $setup->endSetup();
    }

    protected function addOrderStatus($code, $label)
    {
        $statusResource = $this->statusResourceFactory->create();
        $status = $this->statusFactory->create();
        
        $status->setData([
            'status' => $code,
            'label' => $label,
        ]);
        
        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }
        
        $status->assignState($code, true, true);
    }
}
