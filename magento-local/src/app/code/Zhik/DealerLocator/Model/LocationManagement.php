<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Zhik\DealerLocator\Api\LocationManagementInterface;
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
     * @param CollectionFactory $locationCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Curl $curl
     */
    public function __construct(
        CollectionFactory $locationCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        Curl $curl
    ) {
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
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
}