<?php
/**
 * Copyright © Zhik. All rights reserved.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Block\Map;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Dealer Locator Map View Block
 */
class View extends Template
{
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get Google Maps API Key
     *
     * @return string|null
     */
    public function getGoogleMapsApiKey(): ?string
    {
        $apiKey = $this->scopeConfig->getValue(
            'dealerlocator/google_maps/api_key',
            ScopeInterface::SCOPE_STORE
        );
        
        return $apiKey ? trim($apiKey) : null;
    }

    /**
     * Get search endpoint URL
     *
     * @return string
     */
    public function getSearchUrl(): string
    {
        return $this->getUrl('dealerlocator/location/search');
    }

    /**
     * Get location details endpoint URL
     *
     * @return string
     */
    public function getLocationDetailsUrl(): string
    {
        return $this->getUrl('dealerlocator/location/details');
    }

    /**
     * Get map configuration
     *
     * @return array
     */
    public function getMapConfig(): array
    {
        $mapStyle = $this->scopeConfig->getValue(
            'dealerlocator/map/map_style',
            ScopeInterface::SCOPE_STORE
        );
        
        // Parse map style JSON if provided
        $mapStyleArray = [];
        if ($mapStyle) {
            try {
                $mapStyleArray = json_decode($mapStyle, true) ?: [];
            } catch (\Exception $e) {
                $mapStyleArray = [];
            }
        }
        
        return [
            'apiKey' => $this->getGoogleMapsApiKey(),
            'searchUrl' => $this->getSearchUrl(),
            'detailsUrl' => $this->getLocationDetailsUrl(),
            'geocodeUrl' => $this->getUrl('dealerlocator/map/geocode'),
            'defaultLat' => $this->scopeConfig->getValue(
                'dealerlocator/map/default_latitude',
                ScopeInterface::SCOPE_STORE
            ) ?: '40.7128',
            'defaultLng' => $this->scopeConfig->getValue(
                'dealerlocator/map/default_longitude',
                ScopeInterface::SCOPE_STORE
            ) ?: '-74.0060',
            'defaultZoom' => (int)$this->scopeConfig->getValue(
                'dealerlocator/map/default_zoom',
                ScopeInterface::SCOPE_STORE
            ) ?: 10,
            'defaultRadius' => (int)$this->scopeConfig->getValue(
                'dealerlocator/geolocation/default_radius',
                ScopeInterface::SCOPE_STORE
            ) ?: 50,
            'mapStyle' => $mapStyleArray,
            'clusterEnabled' => $this->scopeConfig->isSetFlag(
                'dealerlocator/map/cluster_enabled',
                ScopeInterface::SCOPE_STORE
            ),
            'clusterGridSize' => (int)$this->scopeConfig->getValue(
                'dealerlocator/map/cluster_grid_size',
                ScopeInterface::SCOPE_STORE
            ) ?: 60,
            'clusterMinSize' => (int)$this->scopeConfig->getValue(
                'dealerlocator/map/cluster_min_size',
                ScopeInterface::SCOPE_STORE
            ) ?: 3
        ];
    }

    /**
     * Check if map is enabled
     *
     * @return bool
     */
    public function isMapEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'dealerlocator/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }
}