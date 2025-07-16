<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Zhik\DealerLocator\Api\Data\LocationInterface;
use Zhik\DealerLocator\Api\Data\LocationInterfaceFactory;
use Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface;
use Zhik\DealerLocator\Api\Data\LocationSearchResultsInterfaceFactory;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Model\ResourceModel\Location as ResourceLocation;
use Zhik\DealerLocator\Model\ResourceModel\Location\CollectionFactory as LocationCollectionFactory;
use Zhik\DealerLocator\Helper\Email as EmailHelper;

/**
 * Location repository implementation
 */
class LocationRepository implements LocationRepositoryInterface
{
    /**
     * @var ResourceLocation
     */
    private $resource;

    /**
     * @var LocationInterfaceFactory
     */
    private $locationFactory;

    /**
     * @var LocationCollectionFactory
     */
    private $locationCollectionFactory;

    /**
     * @var LocationSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @param ResourceLocation $resource
     * @param LocationInterfaceFactory $locationFactory
     * @param LocationCollectionFactory $locationCollectionFactory
     * @param LocationSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param DateTime $dateTime
     * @param EmailHelper $emailHelper
     */
    public function __construct(
        ResourceLocation $resource,
        LocationInterfaceFactory $locationFactory,
        LocationCollectionFactory $locationCollectionFactory,
        LocationSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        DateTime $dateTime,
        EmailHelper $emailHelper
    ) {
        $this->resource = $resource;
        $this->locationFactory = $locationFactory;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->dateTime = $dateTime;
        $this->emailHelper = $emailHelper;
    }

    /**
     * @inheritdoc
     */
    public function save(LocationInterface $location): LocationInterface
    {
        try {
            // Handle versioning for approved locations
            // Only create new version if it's an existing approved location being edited
            // Don't create new version during the approval process itself
            if ($location->getLocationId() && 
                $location->getStatus() === LocationInterface::STATUS_APPROVED &&
                $location->getOrigData('status') === LocationInterface::STATUS_APPROVED &&
                $location->hasDataChanges()) {
                
                // Check if this is actually a content change, not just approval
                $ignoredFields = ['status', 'approved_at', 'approved_by', 'rejection_reason', 'updated_at'];
                $hasContentChanges = false;
                
                foreach ($location->getData() as $key => $value) {
                    if (!in_array($key, $ignoredFields) && 
                        $location->getOrigData($key) !== $value) {
                        $hasContentChanges = true;
                        break;
                    }
                }
                
                if ($hasContentChanges) {
                    // Create new version
                    $newLocation = $this->locationFactory->create();
                    $newLocation->setData($location->getData());
                    $newLocation->setLocationId(null);
                    $newLocation->setParentId($location->getLocationId());
                    $newLocation->setStatus(LocationInterface::STATUS_PENDING);
                    $newLocation->setIsLatest(true);
                    $newLocation->setApprovedAt(null);
                    $newLocation->setApprovedBy(null);
                    
                    // Mark old version as not latest
                    $location->setIsLatest(false);
                    $this->resource->save($location);
                    
                    // Save new version
                    $this->resource->save($newLocation);
                    
                    // Save tags if any
                    if ($tagIds = $location->getData('tag_ids')) {
                        $this->resource->saveLocationTags($newLocation->getLocationId(), $tagIds);
                    }
                    
                    return $newLocation;
                }
            }
            
            // Normal save
            $this->resource->save($location);
            
            // Save tags if any
            if ($tagIds = $location->getData('tag_ids')) {
                $this->resource->saveLocationTags($location->getLocationId(), $tagIds);
            }
            
            return $location;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    /**
     * @inheritdoc
     */
    public function getById(int $locationId): LocationInterface
    {
        $location = $this->locationFactory->create();
        $this->resource->load($location, $locationId);
        if (!$location->getLocationId()) {
            throw new NoSuchEntityException(__('Location with ID "%1" does not exist.', $locationId));
        }
        
        // Load tags
        $tagIds = $this->resource->getLocationTags($locationId);
        $location->setData('tag_ids', $tagIds);
        
        return $location;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): LocationSearchResultsInterface
    {
        $collection = $this->locationCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        
        $items = [];
        foreach ($collection->getItems() as $model) {
            $items[] = $model;
        }
        
        // Load tags for all locations in a single query
        $this->loadTagsForLocations($items);
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function delete(LocationInterface $location): bool
    {
        try {
            $this->resource->delete($location);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $locationId): bool
    {
        return $this->delete($this->getById($locationId));
    }

    /**
     * @inheritdoc
     */
    public function getByCustomerId(int $customerId): array
    {
        $collection = $this->locationCollectionFactory->create();
        $collection->addCustomerFilter($customerId)
                   ->addIsLatestFilter(true);
        
        $items = [];
        foreach ($collection->getItems() as $location) {
            $items[] = $location;
        }
        
        // Load tags for all locations in a single query
        $this->loadTagsForLocations($items);
        
        return $items;
    }

    /**
     * @inheritdoc
     */
    public function approve(int $locationId, int $adminUserId): LocationInterface
    {
        $location = $this->getById($locationId);
        
        if ($location->getStatus() === LocationInterface::STATUS_APPROVED) {
            throw new LocalizedException(__('Location is already approved.'));
        }
        
        $location->setStatus(LocationInterface::STATUS_APPROVED);
        $location->setApprovedAt($this->dateTime->gmtDate());
        $location->setApprovedBy($adminUserId);
        $location->setRejectionReason(null);
        $location->setIsLatest(true);
        
        // Mark any other versions as not latest
        if ($location->getParentId()) {
            $this->markOtherVersionsAsNotLatest((int)$location->getParentId(), (int)$location->getLocationId());
        } else {
            // This is approving the original location, mark any child versions as not latest
            $this->markChildVersionsAsNotLatest((int)$location->getLocationId());
        }
        
        $savedLocation = $this->save($location);
        
        // Send approval email to customer
        $this->emailHelper->sendLocationApproved($savedLocation);
        
        return $savedLocation;
    }

    /**
     * @inheritdoc
     */
    public function reject(int $locationId, string $reason, int $adminUserId): LocationInterface
    {
        $location = $this->getById($locationId);
        
        if ($location->getStatus() === LocationInterface::STATUS_REJECTED) {
            throw new LocalizedException(__('Location is already rejected.'));
        }
        
        $location->setStatus(LocationInterface::STATUS_REJECTED);
        $location->setRejectionReason($reason);
        $location->setApprovedAt($this->dateTime->gmtDate());
        $location->setApprovedBy($adminUserId);
        
        $savedLocation = $this->save($location);
        
        // Send rejection email to customer
        $this->emailHelper->sendLocationRejected($savedLocation);
        
        return $savedLocation;
    }

    /**
     * Mark other versions of a location as not latest
     *
     * @param int $parentId
     * @param int $currentLocationId
     * @return void
     */
    private function markOtherVersionsAsNotLatest(int $parentId, int $currentLocationId): void
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getMainTable();
        
        $connection->update(
            $table,
            ['is_latest' => 0],
            [
                'parent_id = ?' => $parentId,
                'location_id != ?' => $currentLocationId
            ]
        );
        
        // Also update the parent location
        $connection->update(
            $table,
            ['is_latest' => 0],
            ['location_id = ?' => $parentId]
        );
    }

    /**
     * Mark child versions of a location as not latest
     *
     * @param int $parentId
     * @return void
     */
    private function markChildVersionsAsNotLatest(int $parentId): void
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getMainTable();
        
        $connection->update(
            $table,
            ['is_latest' => 0],
            ['parent_id = ?' => $parentId]
        );
    }

    /**
     * Load tags for multiple locations in a single query
     *
     * @param LocationInterface[] $locations
     * @return void
     */
    private function loadTagsForLocations(array $locations): void
    {
        if (empty($locations)) {
            return;
        }

        // Extract location IDs
        $locationIds = [];
        $locationMap = [];
        foreach ($locations as $location) {
            $locationId = $location->getLocationId();
            $locationIds[] = $locationId;
            $locationMap[$locationId] = $location;
            // Initialize empty tag array
            $location->setData('tag_ids', []);
        }

        // Load all tags for these locations in a single query
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTable('zhik_dealer_location_tags'), ['location_id', 'tag_id'])
            ->where('location_id IN (?)', $locationIds);

        $tagData = $connection->fetchAll($select);

        // Map tags to their respective locations
        foreach ($tagData as $row) {
            $locationId = (int)$row['location_id'];
            $tagId = (int)$row['tag_id'];
            
            if (isset($locationMap[$locationId])) {
                $location = $locationMap[$locationId];
                $tagIds = $location->getData('tag_ids') ?: [];
                $tagIds[] = $tagId;
                $location->setData('tag_ids', $tagIds);
            }
        }
    }
}