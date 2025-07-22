<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Zhik\DealerLocator\Api\LocationSearchInterface;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Api\Data\LocationInterface;
use Zhik\DealerLocator\Api\Data\LocationSearchResultsInterfaceFactory;
use Zhik\DealerLocator\Model\ResourceModel\Location\CollectionFactory;

/**
 * Location search implementation
 */
class LocationSearch implements LocationSearchInterface
{
    /**
     * @var LocationRepositoryInterface
     */
    private $locationRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var LocationSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param LocationRepositoryInterface $locationRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param LocationSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        LocationRepositoryInterface $locationRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        LocationSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->locationRepository = $locationRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function search(
        ?string $query = null,
        ?array $tagIds = null,
        ?string $country = null,
        ?string $state = null,
        ?string $city = null,
        ?int $limit = null,
        ?int $page = null
    ): \Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface {
        // Include both approved and pending deletion locations
        $statusFilter1 = $this->filterBuilder
            ->setField('status')
            ->setValue(LocationInterface::STATUS_APPROVED)
            ->setConditionType('eq')
            ->create();
            
        $statusFilter2 = $this->filterBuilder
            ->setField('status')
            ->setValue(LocationInterface::STATUS_PENDING_DELETION)
            ->setConditionType('eq')
            ->create();
            
        $statusFilterGroup = $this->filterGroupBuilder
            ->addFilter($statusFilter1)
            ->addFilter($statusFilter2)
            ->create();
            
        $filterGroups = [$statusFilterGroup];
        $this->searchCriteriaBuilder->addFilter('is_latest', 1);

        if ($query !== null) {
            // Search in multiple fields
            $fields = ['name', 'address', 'city', 'description'];
            foreach ($fields as $field) {
                $filter = $this->filterBuilder
                    ->setField($field)
                    ->setConditionType('like')
                    ->setValue('%' . $query . '%')
                    ->create();
                
                $filterGroup = $this->filterGroupBuilder
                    ->addFilter($filter)
                    ->create();
                
                $filterGroups[] = $filterGroup;
            }
        }
        
        $this->searchCriteriaBuilder->setFilterGroups($filterGroups);

        if ($country !== null) {
            $this->searchCriteriaBuilder->addFilter('country', $country);
        }

        if ($state !== null) {
            $this->searchCriteriaBuilder->addFilter('state', $state);
        }

        if ($city !== null) {
            $this->searchCriteriaBuilder->addFilter('city', $city);
        }

        if ($limit !== null) {
            $this->searchCriteriaBuilder->setPageSize($limit);
        }

        if ($page !== null) {
            $this->searchCriteriaBuilder->setCurrentPage($page);
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->locationRepository->getList($searchCriteria);

        // Filter by tags if provided
        if ($tagIds !== null && !empty($tagIds)) {
            $items = [];
            foreach ($searchResults->getItems() as $location) {
                $locationTags = $location->getData('tag_ids') ?: [];
                if (array_intersect($tagIds, $locationTags)) {
                    $items[] = $location;
                }
            }
            $searchResults->setItems($items);
            $searchResults->setTotalCount(count($items));
        }

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function searchNearby(
        float $latitude,
        float $longitude,
        float $radius,
        ?array $tagIds = null,
        ?int $limit = null
    ): \Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface {
        $collection = $this->collectionFactory->create();
        
        // Base filters - include both approved and pending deletion locations
        $collection->addFieldToFilter('status', [
            'in' => [
                LocationInterface::STATUS_APPROVED,
                LocationInterface::STATUS_PENDING_DELETION
            ]
        ]);
        $collection->addFieldToFilter('is_latest', 1);
        
        // Calculate distance using Haversine formula
        $earthRadius = 6371; // Earth radius in kilometers
        
        $collection->getSelect()->columns([
            'distance' => new \Zend_Db_Expr(
                "({$earthRadius} * acos(cos(radians({$latitude})) * cos(radians(latitude)) * " .
                "cos(radians(longitude) - radians({$longitude})) + sin(radians({$latitude})) * " .
                "sin(radians(latitude))))"
            )
        ]);
        
        // Filter by radius
        $collection->getSelect()->having('distance <= ?', $radius);
        
        // Order by distance
        $collection->getSelect()->order('distance ASC');
        
        if ($limit !== null) {
            $collection->setPageSize($limit);
        }
        
        // Create search results
        $searchResults = $this->searchResultsFactory->create();
        $items = [];
        
        foreach ($collection as $location) {
            // Load tags for each location
            $locationTags = $location->getResource()->getLocationTags($location->getLocationId());
            $location->setData('tag_ids', $locationTags);
            
            // Filter by tags if provided
            if ($tagIds !== null && !empty($tagIds)) {
                if (!array_intersect($tagIds, $locationTags)) {
                    continue;
                }
            }
            
            // Add distance to location data
            $location->setData('distance', $location->getData('distance'));
            $items[] = $location;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount(count($items));
        
        return $searchResults;
    }
}