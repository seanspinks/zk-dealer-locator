<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Api;

/**
 * Location approval interface
 *
 * @api
 */
interface LocationApprovalInterface
{
    /**
     * Approve location
     *
     * @param int $locationId
     * @return \Zhik\DealerLocator\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function approve(int $locationId): \Zhik\DealerLocator\Api\Data\LocationInterface;

    /**
     * Reject location
     *
     * @param int $locationId
     * @param string $reason
     * @return \Zhik\DealerLocator\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reject(int $locationId, string $reason): \Zhik\DealerLocator\Api\Data\LocationInterface;
}