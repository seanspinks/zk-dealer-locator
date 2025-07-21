<?php
/**
 * Copyright © Zhik. All rights reserved.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Map;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Zhik\DealerLocator\Model\Service\GeolocationService;
use Magento\Framework\App\RequestInterface;

/**
 * Map configuration endpoint for embedded iframe
 */
class Config implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var GeolocationService
     */
    protected GeolocationService $geolocationService;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * Constructor
     *
     * @param JsonFactory $jsonFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param GeolocationService $geolocationService
     * @param RequestInterface $request
     */
    public function __construct(
        JsonFactory $jsonFactory,
        ScopeConfigInterface $scopeConfig,
        GeolocationService $geolocationService,
        RequestInterface $request
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->geolocationService = $geolocationService;
        $this->request = $request;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        
        // Get map style from configuration
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
        
        // Default fallback coordinates (New York)
        $fallbackLat = 40.7128;
        $fallbackLng = -74.0060;
        
        // Head office coordinates (39 Herbert Street, St Leonards, NSW 2065, AU)
        $headOfficeLat = -33.8226;
        $headOfficeLng = 151.1929;
        
        // Try to get user's location from IP
        $userLat = null;
        $userLng = null;
        $userCountry = 'US';
        $userCity = '';
        $userRegion = '';
        
        try {
            // Get client IP
            $clientIp = $this->getClientIp();
            
            // Get location from IP
            if ($clientIp && $clientIp !== '127.0.0.1') {
                $location = $this->geolocationService->getLocationByIp($clientIp);
                if ($location && isset($location['latitude']) && isset($location['longitude'])) {
                    $userLat = $location['latitude'];
                    $userLng = $location['longitude'];
                    $userCountry = $location['country'] ?? 'AU';
                    
                    // Add city info for debugging
                    $userCity = $location['city'] ?? '';
                    $userRegion = $location['region'] ?? '';
                }
            }
        } catch (\Exception $e) {
            // Fall back to head office location if geo IP fails
        }
        
        // Get configured defaults or use head office
        $configuredLat = $this->scopeConfig->getValue(
            'dealerlocator/map/default_latitude',
            ScopeInterface::SCOPE_STORE
        );
        $configuredLng = $this->scopeConfig->getValue(
            'dealerlocator/map/default_longitude',
            ScopeInterface::SCOPE_STORE
        );
        
        // Use geo IP location, or configured location, or New York as fallback
        $defaultLat = $userLat ?: ($configuredLat ?: $fallbackLat);
        $defaultLng = $userLng ?: ($configuredLng ?: $fallbackLng);
        
        // Get other map configuration
        $config = [
            'mapStyle' => $mapStyleArray,
            'defaultLat' => (string)$defaultLat,
            'defaultLng' => (string)$defaultLng,
            'userLat' => (string)($userLat ?: $fallbackLat),
            'userLng' => (string)($userLng ?: $fallbackLng),
            'userCountry' => $userCountry,
            'headOfficeLat' => (string)$headOfficeLat,
            'headOfficeLng' => (string)$headOfficeLng,
            'detectedIp' => $clientIp ?? 'unknown',
            'geoIpUsed' => $userLat !== null,
            'userCity' => $userCity ?? '',
            'userRegion' => $userRegion ?? '',
            'defaultZoom' => (int)$this->scopeConfig->getValue(
                'dealerlocator/map/default_zoom',
                ScopeInterface::SCOPE_STORE
            ) ?: 10,
            'defaultRadius' => (int)$this->scopeConfig->getValue(
                'dealerlocator/geolocation/default_radius',
                ScopeInterface::SCOPE_STORE
            ) ?: 50,
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
        
        // Add CORS headers for iframe usage
        $result->setHttpResponseCode(200);
        $result->setHeader('Access-Control-Allow-Origin', '*');
        $result->setHeader('Access-Control-Allow-Methods', 'GET');
        $result->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        
        return $result->setData($config);
    }
    
    /**
     * Get client IP address
     * 
     * @return string|null
     */
    protected function getClientIp()
    {
        // Try to get IP from request object first
        $ip = $this->request->getClientIp();
        
        if ($ip) {
            return $ip;
        }
        
        // Fallback to geolocation service method
        return $this->geolocationService->getClientIp();
    }
}