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
use Zhik\DealerLocator\Api\Data\TagInterface;
use Zhik\DealerLocator\Api\Data\TagInterfaceFactory;
use Zhik\DealerLocator\Api\Data\TagSearchResultsInterface;
use Zhik\DealerLocator\Api\Data\TagSearchResultsInterfaceFactory;
use Zhik\DealerLocator\Api\TagRepositoryInterface;
use Zhik\DealerLocator\Model\ResourceModel\Tag as ResourceTag;
use Zhik\DealerLocator\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

/**
 * Tag repository implementation
 */
class TagRepository implements TagRepositoryInterface
{
    /**
     * @var ResourceTag
     */
    private $resource;

    /**
     * @var TagInterfaceFactory
     */
    private $tagFactory;

    /**
     * @var TagCollectionFactory
     */
    private $tagCollectionFactory;

    /**
     * @var TagSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourceTag $resource
     * @param TagInterfaceFactory $tagFactory
     * @param TagCollectionFactory $tagCollectionFactory
     * @param TagSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceTag $resource,
        TagInterfaceFactory $tagFactory,
        TagCollectionFactory $tagCollectionFactory,
        TagSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->tagFactory = $tagFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function save(TagInterface $tag): TagInterface
    {
        try {
            $this->resource->save($tag);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $tag;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $tagId): TagInterface
    {
        $tag = $this->tagFactory->create();
        $this->resource->load($tag, $tagId);
        if (!$tag->getTagId()) {
            throw new NoSuchEntityException(__('Tag with ID "%1" does not exist.', $tagId));
        }
        return $tag;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TagSearchResultsInterface
    {
        $collection = $this->tagCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        
        $items = [];
        foreach ($collection->getItems() as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function delete(TagInterface $tag): bool
    {
        try {
            // Check if tag is in use
            $usageCount = $this->resource->getUsageCount($tag->getTagId());
            if ($usageCount > 0) {
                throw new LocalizedException(
                    __('Cannot delete tag "%1" because it is used by %2 location(s).', 
                        $tag->getTagName(), 
                        $usageCount
                    )
                );
            }
            
            $this->resource->delete($tag);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $tagId): bool
    {
        return $this->delete($this->getById($tagId));
    }

    /**
     * @inheritdoc
     */
    public function getActiveTags(): array
    {
        $collection = $this->tagCollectionFactory->create();
        $collection->addActiveFilter()
                   ->setSortOrder();
        
        return $collection->getItems();
    }
}