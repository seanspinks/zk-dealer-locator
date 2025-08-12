<?php
/**
 * Copyright © Zhik. All rights reserved.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Location;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\RequestInterface;
use Zhik\DealerLocator\Api\LocationSearchInterface;
use Psr\Log\LoggerInterface;

/**
 * Location Search Controller
 */
class Search implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var LocationSearchInterface
     */
    protected LocationSearchInterface $locationSearch;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param LocationSearchInterface $locationSearch
     * @param LoggerInterface $logger
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        LocationSearchInterface $locationSearch,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->locationSearch = $locationSearch;
        $this->logger = $logger;
    }

    /**
     * Execute search action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            $params = json_decode($this->request->getContent(), true);
            
            if (!isset($params['lat']) || !isset($params['lng'])) {
                return $resultJson->setData([
                    'error' => true,
                    'message' => __('Invalid search parameters')
                ]);
            }
            
            $latitude = (float) $params['lat'];
            $longitude = (float) $params['lng'];
            $radiusMiles = isset($params['radius']) ? (int) $params['radius'] : 50;
            $tagIds = isset($params['tags']) ? $params['tags'] : [];
            
            // Convert miles to kilometers (1 mile = 1.60934 km)
            $radiusKm = $radiusMiles * 1.60934;
            
            // If radius is 0, search globally (set a very large radius)
            if ($radiusMiles === 0) {
                $radiusKm = 40000; // Earth's circumference in km
            }
            
            $searchResults = $this->locationSearch->searchNearby(
                $latitude,
                $longitude,
                (float) $radiusKm,
                $tagIds
            );
            
            // Convert to array for JSON response
            $results = [];
            foreach ($searchResults->getItems() as $location) {
                // Get tag names for this location
                $tagNames = [];
                if ($location->getData('tag_ids')) {
                    $tagIds = $location->getData('tag_ids');
                    // Load tag names from the database
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                    $connection = $resource->getConnection();
                    
                    if (!empty($tagIds)) {
                        $select = $connection->select()
                            ->from($resource->getTableName('zhik_dealer_tags'), ['tag_name'])
                            ->where('tag_id IN (?)', $tagIds);
                        $tagNames = $connection->fetchCol($select);
                    }
                }
                
                $results[] = [
                    'location_id' => $location->getLocationId(),
                    'name' => $location->getName(),
                    'address' => $location->getAddress(),
                    'city' => $location->getCity(),
                    'state' => $location->getState(),
                    'zip' => $location->getPostalCode(),
                    'country' => $location->getCountry(),
                    'phone' => $location->getPhone(),
                    'email' => $location->getEmail(),
                    'website' => $location->getWebsite(),
                    'tags' => implode(', ', $tagNames),
                    'latitude' => $location->getLatitude(),
                    'longitude' => $location->getLongitude(),
                    'status' => $location->getStatus(),
                    'distance' => $location->getData('distance')
                ];
            }
            
            return $resultJson->setData($results);
            
        } catch (\Exception $e) {
            $this->logger->error('Dealer location search error: ' . $e->getMessage());
            
            return $resultJson->setData([
                'error' => true,
                'message' => __('An error occurred during search. Please try again.')
            ]);
        }
    }
}