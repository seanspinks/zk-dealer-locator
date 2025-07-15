<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Zhik\DealerLocator\Api\LocationManagementInterface;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Model\ResourceModel\Location\CollectionFactory;

/**
 * Location management service
 */
class LocationManagement implements LocationManagementInterface
{
    /**
     * @var CollectionFactory
     */
    private $locationCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var LocationRepositoryInterface
     */
    private $locationRepository;

    /**
     * @param CollectionFactory $locationCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Curl $curl
     * @param LocationRepositoryInterface $locationRepository
     */
    public function __construct(
        CollectionFactory $locationCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        LocationRepositoryInterface $locationRepository
    ) {
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->locationRepository = $locationRepository;
    }

    /**
     * @inheritdoc
     */
    public function getLocationsWithinRadius(float $latitude, float $longitude, float $radius): array
    {
        $collection = $this->locationCollectionFactory->create();
        $collection->addStatusFilter('approved')
                   ->addIsLatestFilter(true)
                   ->addDistanceFilter($latitude, $longitude, $radius);
        
        return $collection->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getLocationsByTags(array $tagIds): array
    {
        if (empty($tagIds)) {
            return [];
        }
        
        $collection = $this->locationCollectionFactory->create();
        $collection->addStatusFilter('approved')
                   ->addIsLatestFilter(true)
                   ->addTagFilter($tagIds);
        
        return $collection->getItems();
    }

    /**
     * @inheritdoc
     */
    public function validateAddress(string $address): array
    {
        $apiKey = $this->getGoogleMapsApiKey();
        if (!$apiKey) {
            return ['error' => __('Google Maps API key is not configured.')];
        }
        
        $url = sprintf(
            'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s',
            urlencode($address),
            $apiKey
        );
        
        $this->curl->get($url);
        $response = json_decode($this->curl->getBody(), true);
        
        if ($response['status'] === 'OK' && !empty($response['results'])) {
            $result = $response['results'][0];
            return [
                'formatted_address' => $result['formatted_address'],
                'latitude' => $result['geometry']['location']['lat'],
                'longitude' => $result['geometry']['location']['lng'],
                'components' => $result['address_components']
            ];
        }
        
        return ['error' => __('Address could not be validated.')];
    }

    /**
     * @inheritdoc
     */
    public function geocodeAddress(string $address): array
    {
        return $this->validateAddress($address);
    }

    /**
     * Get Google Maps API key from configuration
     *
     * @return string|null
     */
    private function getGoogleMapsApiKey(): ?string
    {
        return $this->scopeConfig->getValue('dealerlocator/google_maps/api_key');
    }

    /**
     * @inheritdoc
     */
    public function getCustomerLocations(int $customerId): array
    {
        return $this->locationRepository->getByCustomerId($customerId);
    }

    /**
     * @inheritdoc
     */
    public function saveLocation(\Zhik\DealerLocator\Api\Data\LocationInterface $location): \Zhik\DealerLocator\Api\Data\LocationInterface
    {
        return $this->locationRepository->save($location);
    }

    /**
     * @inheritdoc
     */
    public function updateLocation(int $locationId, \Zhik\DealerLocator\Api\Data\LocationInterface $location): \Zhik\DealerLocator\Api\Data\LocationInterface
    {
        $existingLocation = $this->locationRepository->getById($locationId);
        
        // Verify ownership
        if ($existingLocation->getCustomerId() != $location->getCustomerId()) {
            throw new LocalizedException(__('You are not authorized to update this location.'));
        }
        
        $location->setLocationId($locationId);
        return $this->locationRepository->save($location);
    }

    /**
     * @inheritdoc
     */
    public function deleteLocation(int $locationId, int $customerId): bool
    {
        $location = $this->locationRepository->getById($locationId);
        
        // Verify ownership
        if ($location->getCustomerId() != $customerId) {
            throw new LocalizedException(__('You are not authorized to delete this location.'));
        }
        
        return $this->locationRepository->deleteById($locationId);
    }
}