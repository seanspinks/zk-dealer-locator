<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Zhik\DealerLocator\Api\Data\LocationInterface;
use Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface;

/**
 * Dealer location CRUD interface
 * @api
 */
interface LocationRepositoryInterface
{
    /**
     * Save location
     *
     * @param LocationInterface $location
     * @return LocationInterface
     * @throws LocalizedException
     */
    public function save(LocationInterface $location): LocationInterface;

    /**
     * Get location by ID
     *
     * @param int $locationId
     * @return LocationInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $locationId): LocationInterface;

    /**
     * Get list of locations
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return LocationSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): LocationSearchResultsInterface;

    /**
     * Delete location
     *
     * @param LocationInterface $location
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(LocationInterface $location): bool;

    /**
     * Delete location by ID
     *
     * @param int $locationId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $locationId): bool;

    /**
     * Get locations by customer ID
     *
     * @param int $customerId
     * @return LocationInterface[]
     * @throws LocalizedException
     */
    public function getByCustomerId(int $customerId): array;

    /**
     * Approve location
     *
     * @param int $locationId
     * @param int $adminUserId
     * @return LocationInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function approve(int $locationId, int $adminUserId): LocationInterface;

    /**
     * Reject location
     *
     * @param int $locationId
     * @param string $reason
     * @param int $adminUserId
     * @return LocationInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function reject(int $locationId, string $reason, int $adminUserId): LocationInterface;
}