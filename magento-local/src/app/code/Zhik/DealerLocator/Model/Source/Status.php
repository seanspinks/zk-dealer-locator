<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Zhik\DealerLocator\Api\Data\LocationInterface;

/**
 * Location status source model
 */
class Status implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => LocationInterface::STATUS_PENDING, 'label' => __('Pending')],
            ['value' => LocationInterface::STATUS_APPROVED, 'label' => __('Approved')],
            ['value' => LocationInterface::STATUS_REJECTED, 'label' => __('Rejected')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            LocationInterface::STATUS_PENDING => __('Pending'),
            LocationInterface::STATUS_APPROVED => __('Approved'),
            LocationInterface::STATUS_REJECTED => __('Rejected')
        ];
    }
}