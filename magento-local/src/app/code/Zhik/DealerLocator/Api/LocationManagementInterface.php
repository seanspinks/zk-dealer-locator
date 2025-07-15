<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Api;

/**
 * Location management interface
 * @api
 */
interface LocationManagementInterface
{
    /**
     * Get locations within radius
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $radius Radius in kilometers
     * @return \Zhik\DealerLocator\Api\Data\LocationInterface[]
     */
    public function getLocationsWithinRadius(float $latitude, float $longitude, float $radius): array;

    /**
     * Get locations by tags
     *
     * @param int[] $tagIds
     * @return \Zhik\DealerLocator\Api\Data\LocationInterface[]
     */
    public function getLocationsByTags(array $tagIds): array;

    /**
     * Validate address using Google Maps API
     *
     * @param string $address
     * @return array
     */
    public function validateAddress(string $address): array;

    /**
     * Geocode address to get coordinates
     *
     * @param string $address
     * @return array
     */
    public function geocodeAddress(string $address): array;

    /**
     * Get customer locations
     *
     * @param int $customerId
     * @return \Zhik\DealerLocator\Api\Data\LocationInterface[]
     */
    public function getCustomerLocations(int $customerId): array;

    /**
     * Save location for customer
     *
     * @param \Zhik\DealerLocator\Api\Data\LocationInterface $location
     * @return \Zhik\DealerLocator\Api\Data\LocationInterface
     */
    public function saveLocation(\Zhik\DealerLocator\Api\Data\LocationInterface $location): \Zhik\DealerLocator\Api\Data\LocationInterface;

    /**
     * Update location for customer
     *
     * @param int $locationId
     * @param \Zhik\DealerLocator\Api\Data\LocationInterface $location
     * @return \Zhik\DealerLocator\Api\Data\LocationInterface
     */
    public function updateLocation(int $locationId, \Zhik\DealerLocator\Api\Data\LocationInterface $location): \Zhik\DealerLocator\Api\Data\LocationInterface;

    /**
     * Delete location for customer
     *
     * @param int $locationId
     * @param int $customerId
     * @return bool
     */
    public function deleteLocation(int $locationId, int $customerId): bool;
}