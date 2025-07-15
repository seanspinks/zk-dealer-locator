<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Api;

/**
 * Location search interface
 *
 * @api
 */
interface LocationSearchInterface
{
    /**
     * Search locations
     *
     * @param string|null $query Search query
     * @param string[]|null $tagIds Filter by tag IDs
     * @param string|null $country Filter by country
     * @param string|null $state Filter by state
     * @param string|null $city Filter by city
     * @param int|null $limit Limit results
     * @param int|null $page Page number
     * @return \Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface
     */
    public function search(
        ?string $query = null,
        ?array $tagIds = null,
        ?string $country = null,
        ?string $state = null,
        ?string $city = null,
        ?int $limit = null,
        ?int $page = null
    ): \Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface;

    /**
     * Search nearby locations
     *
     * @param float $latitude Latitude
     * @param float $longitude Longitude
     * @param float $radius Radius in kilometers
     * @param string[]|null $tagIds Filter by tag IDs
     * @param int|null $limit Limit results
     * @return \Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface
     */
    public function searchNearby(
        float $latitude,
        float $longitude,
        float $radius,
        ?array $tagIds = null,
        ?int $limit = null
    ): \Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface;
}