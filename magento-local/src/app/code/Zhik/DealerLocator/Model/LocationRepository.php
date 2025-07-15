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
            if ($location->getLocationId() && 
                $location->getStatus() === LocationInterface::STATUS_APPROVED &&
                $location->hasDataChanges()) {
                
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
            // Load tags for each location
            $tagIds = $this->resource->getLocationTags($model->getLocationId());
            $model->setData('tag_ids', $tagIds);
            $items[] = $model;
        }
        
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
            // Load tags
            $tagIds = $this->resource->getLocationTags($location->getLocationId());
            $location->setData('tag_ids', $tagIds);
            $items[] = $location;
        }
        
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
}