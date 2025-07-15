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
}