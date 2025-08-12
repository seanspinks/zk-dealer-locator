<?php
namespace Zhik\DealerLocator\Controller\Map;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Zhik\DealerLocator\Model\Service\GeolocationService;
use Psr\Log\LoggerInterface;

class Geocode implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;
    
    /**
     * @var GeolocationService
     */
    private $geolocationService;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param JsonFactory $jsonResultFactory
     * @param GeolocationService $geolocationService
     * @param LoggerInterface $logger
     */
    public function __construct(
        JsonFactory $jsonResultFactory,
        GeolocationService $geolocationService,
        LoggerInterface $logger
    ) {
        $this->jsonResultFactory = $jsonResultFactory;
        $this->geolocationService = $geolocationService;
        $this->logger = $logger;
    }
    
    /**
     * Execute geolocation request
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        
        try {
            if (!$this->geolocationService->isEnabled()) {
                return $result->setData([
                    'success' => false,
                    'message' => 'Geolocation service is disabled'
                ]);
            }
            
            $clientIp = $this->geolocationService->getClientIp();
            
            // For development, use a test IP if localhost
            if ($clientIp === '127.0.0.1' || $clientIp === '::1') {
                $clientIp = '8.8.8.8'; // Google's public DNS for testing
            }
            
            $location = $this->geolocationService->getLocationByIp($clientIp);
            
            if ($location) {
                return $result->setData([
                    'success' => true,
                    'location' => $location
                ]);
            } else {
                return $result->setData([
                    'success' => false,
                    'message' => 'Unable to determine location'
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Geolocation error: ' . $e->getMessage());
            
            return $result->setData([
                'success' => false,
                'message' => 'An error occurred while determining location'
            ]);
        }
    }
}