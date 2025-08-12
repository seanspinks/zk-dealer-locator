<?php
namespace Zhik\DealerLocator\Model\Service;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class GeolocationService
{
    const CONFIG_PATH_GEOLOCATION_ENABLED = 'dealerlocator/geolocation/enabled';
    const CONFIG_PATH_GEOLOCATION_SERVICE = 'dealerlocator/geolocation/service';
    const CONFIG_PATH_IPINFO_TOKEN = 'dealerlocator/geolocation/ipinfo_token';
    
    const SERVICE_IPINFO = 'ipinfo';
    const SERVICE_IPAPI = 'ipapi';
    
    /**
     * @var Curl
     */
    private $curl;
    
    /**
     * @var Json
     */
    private $json;
    
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param Curl $curl
     * @param Json $json
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        Curl $curl,
        Json $json,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }
    
    /**
     * Get location by IP address
     * 
     * @param string $ipAddress
     * @return array|null
     */
    public function getLocationByIp($ipAddress)
    {
        if (!$this->isEnabled()) {
            return null;
        }
        
        $service = $this->scopeConfig->getValue(self::CONFIG_PATH_GEOLOCATION_SERVICE);
        
        try {
            switch ($service) {
                case self::SERVICE_IPINFO:
                    return $this->getLocationFromIpInfo($ipAddress);
                case self::SERVICE_IPAPI:
                    return $this->getLocationFromIpApi($ipAddress);
                default:
                    return $this->getLocationFromIpApi($ipAddress); // Default to free service
            }
        } catch (\Exception $e) {
            $this->logger->error('Geolocation service error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if geolocation is enabled
     * 
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_GEOLOCATION_ENABLED);
    }
    
    /**
     * Get location from IPInfo service
     * 
     * @param string $ipAddress
     * @return array|null
     */
    private function getLocationFromIpInfo($ipAddress)
    {
        $token = $this->scopeConfig->getValue(self::CONFIG_PATH_IPINFO_TOKEN);
        $url = "https://ipinfo.io/{$ipAddress}/json";
        
        if ($token) {
            $url .= "?token={$token}";
        }
        
        $this->curl->get($url);
        $response = $this->curl->getBody();
        
        if ($this->curl->getStatus() !== 200) {
            throw new \Exception('IPInfo API request failed');
        }
        
        $data = $this->json->unserialize($response);
        
        if (isset($data['loc'])) {
            list($latitude, $longitude) = explode(',', $data['loc']);
            
            return [
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
                'city' => $data['city'] ?? '',
                'region' => $data['region'] ?? '',
                'country' => $data['country'] ?? '',
                'postal' => $data['postal'] ?? ''
            ];
        }
        
        return null;
    }
    
    /**
     * Get location from IP-API service (free)
     * 
     * @param string $ipAddress
     * @return array|null
     */
    private function getLocationFromIpApi($ipAddress)
    {
        $url = "http://ip-api.com/json/{$ipAddress}";
        
        $this->curl->get($url);
        $response = $this->curl->getBody();
        
        if ($this->curl->getStatus() !== 200) {
            throw new \Exception('IP-API request failed');
        }
        
        $data = $this->json->unserialize($response);
        
        if (isset($data['status']) && $data['status'] === 'success') {
            return [
                'latitude' => (float) $data['lat'],
                'longitude' => (float) $data['lon'],
                'city' => $data['city'] ?? '',
                'region' => $data['regionName'] ?? '',
                'country' => $data['country'] ?? '',
                'postal' => $data['zip'] ?? ''
            ];
        }
        
        return null;
    }
    
    /**
     * Get client IP address
     * 
     * @return string
     */
    public function getClientIp()
    {
        // Check for IP behind proxy
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                
                // Handle comma-separated IPs (when behind multiple proxies)
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // Fallback to localhost for development
        return '127.0.0.1';
    }
}