<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bleumi\PaymentAggregator\Model;

/**
 * @api
 * @since 100.0.2
 */
class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'processing', 'label' => __('Processing')], ['value' => 'complete', 'label' => __('Complete')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['processing' => __('Processing'), 'complete' => __('Complete')];
    }
}
