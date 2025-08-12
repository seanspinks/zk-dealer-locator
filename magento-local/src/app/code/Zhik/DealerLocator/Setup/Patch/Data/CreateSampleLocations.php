<?php
/**
 * Copyright © Zhik. All rights reserved.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zhik\DealerLocator\Api\Data\LocationInterfaceFactory;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Api\Data\TagInterfaceFactory;
use Zhik\DealerLocator\Api\TagRepositoryInterface;
use Zhik\DealerLocator\Api\Data\LocationInterface;

/**
 * Create sample locations for testing
 */
class CreateSampleLocations implements DataPatchInterface
{
    /**
     * @var LocationInterfaceFactory
     */
    private $locationFactory;

    /**
     * @var LocationRepositoryInterface
     */
    private $locationRepository;

    /**
     * @var TagInterfaceFactory
     */
    private $tagFactory;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * Constructor
     *
     * @param LocationInterfaceFactory $locationFactory
     * @param LocationRepositoryInterface $locationRepository
     * @param TagInterfaceFactory $tagFactory
     * @param TagRepositoryInterface $tagRepository
     */
    public function __construct(
        LocationInterfaceFactory $locationFactory,
        LocationRepositoryInterface $locationRepository,
        TagInterfaceFactory $tagFactory,
        TagRepositoryInterface $tagRepository
    ) {
        $this->locationFactory = $locationFactory;
        $this->locationRepository = $locationRepository;
        $this->tagFactory = $tagFactory;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Apply patch
     */
    public function apply()
    {
        // Create tags first
        $retailTag = $this->createTag('Retail Store', 'retail');
        $serviceTag = $this->createTag('Service Center', 'service');
        $authorizedTag = $this->createTag('Authorized Dealer', 'authorized');
        
        // Create sample locations
        $locations = [
            [
                'name' => 'Zhik New York Flagship Store',
                'address' => '350 5th Ave',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10118',
                'country' => 'US',
                'phone' => '(212) 555-0123',
                'email' => 'ny@zhik.com',
                'website' => 'https://zhik.com/stores/ny',
                'latitude' => 40.7484,
                'longitude' => -73.9857,
                'description' => 'Our flagship store in the heart of Manhattan',
                'hours' => 'Mon-Sat: 10am-8pm, Sun: 11am-7pm',
                'tags' => [$retailTag->getTagId(), $authorizedTag->getTagId()]
            ],
            [
                'name' => 'West Marine San Francisco',
                'address' => '366 Jefferson St',
                'city' => 'San Francisco',
                'state' => 'CA',
                'postal_code' => '94133',
                'country' => 'US',
                'phone' => '(415) 555-0234',
                'email' => 'sf@westmarine.com',
                'website' => 'https://westmarine.com',
                'latitude' => 37.8077,
                'longitude' => -122.4188,
                'description' => 'Your local marine supply store',
                'hours' => 'Mon-Sat: 9am-7pm, Sun: 10am-6pm',
                'tags' => [$retailTag->getTagId()]
            ],
            [
                'name' => 'Miami Yacht Service Center',
                'address' => '1635 N Bayshore Dr',
                'city' => 'Miami',
                'state' => 'FL',
                'postal_code' => '33132',
                'country' => 'US',
                'phone' => '(305) 555-0345',
                'email' => 'service@miamiyacht.com',
                'website' => 'https://miamiyacht.com',
                'latitude' => 25.7898,
                'longitude' => -80.1883,
                'description' => 'Full service yacht maintenance and repair',
                'hours' => 'Mon-Fri: 8am-6pm, Sat: 9am-4pm',
                'tags' => [$serviceTag->getTagId(), $authorizedTag->getTagId()]
            ],
            [
                'name' => 'Seattle Sailing Supply',
                'address' => '1900 N Northlake Way',
                'city' => 'Seattle',
                'state' => 'WA',
                'postal_code' => '98103',
                'country' => 'US',
                'phone' => '(206) 555-0456',
                'email' => 'info@seattlesailing.com',
                'website' => 'https://seattlesailing.com',
                'latitude' => 47.6488,
                'longitude' => -122.3349,
                'description' => 'Everything for the Pacific Northwest sailor',
                'hours' => 'Mon-Sat: 9am-6pm, Sun: 10am-5pm',
                'tags' => [$retailTag->getTagId()]
            ],
            [
                'name' => 'Boston Harbor Marina',
                'address' => '1 Marina Park Dr',
                'city' => 'Boston',
                'state' => 'MA',
                'postal_code' => '02210',
                'country' => 'US',
                'phone' => '(617) 555-0567',
                'email' => 'info@bostonharbormarina.com',
                'website' => 'https://bostonharbormarina.com',
                'latitude' => 42.3554,
                'longitude' => -71.0440,
                'description' => 'Full-service marina with Zhik gear shop',
                'hours' => 'Daily: 7am-7pm',
                'tags' => [$retailTag->getTagId(), $serviceTag->getTagId()]
            ]
        ];
        
        foreach ($locations as $locationData) {
            $tags = $locationData['tags'];
            unset($locationData['tags']);
            
            $location = $this->locationFactory->create();
            $location->setData($locationData);
            $location->setStatus(LocationInterface::STATUS_APPROVED);
            $location->setCustomerId(1); // Default admin customer
            $location->setIsLatest(true);
            
            try {
                $savedLocation = $this->locationRepository->save($location);
                
                // Associate tags
                if (!empty($tags)) {
                    $connection = $savedLocation->getResource()->getConnection();
                    $tagTable = $savedLocation->getResource()->getTable('zhik_dealer_location_tags');
                    
                    foreach ($tags as $tagId) {
                        $connection->insertOnDuplicate($tagTable, [
                            'location_id' => $savedLocation->getLocationId(),
                            'tag_id' => $tagId
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Skip if location already exists
                continue;
            }
        }
        
        return $this;
    }

    /**
     * Create a tag
     *
     * @param string $name
     * @param string $code
     * @return \Zhik\DealerLocator\Api\Data\TagInterface
     */
    private function createTag(string $name, string $code)
    {
        $tag = $this->tagFactory->create();
        $tag->setTagName($name);
        $tag->setTagSlug($code);
        $tag->setIsActive(true);
        
        try {
            return $this->tagRepository->save($tag);
        } catch (\Exception $e) {
            // Try to load existing tag
            $searchCriteriaBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
            
            $searchCriteria = $searchCriteriaBuilder
                ->addFilter('tag_slug', $code)
                ->create();
                
            $searchResults = $this->tagRepository->getList($searchCriteria);
            
            $items = $searchResults->getItems();
            if (!empty($items)) {
                return reset($items);
            }
            
            throw $e;
        }
    }

    /**
     * Get aliases
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases
     */
    public function getAliases()
    {
        return [];
    }
}