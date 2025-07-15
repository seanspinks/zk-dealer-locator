<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model\Location\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Zhik\DealerLocator\Api\Data\LocationInterface;

/**
 * Status source model
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
}