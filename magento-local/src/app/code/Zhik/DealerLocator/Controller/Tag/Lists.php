<?php
/**
 * Copyright © Zhik. All rights reserved.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Tag;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Zhik\DealerLocator\Api\TagRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

/**
 * Tag List Controller
 */
class Lists implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var TagRepositoryInterface
     */
    protected TagRepositoryInterface $tagRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param JsonFactory $resultJsonFactory
     * @param TagRepositoryInterface $tagRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        TagRepositoryInterface $tagRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tagRepository = $tagRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            // Get only active tags
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('is_active', 1)
                ->create();
                
            $searchResults = $this->tagRepository->getList($searchCriteria);
            
            $tags = [];
            foreach ($searchResults->getItems() as $tag) {
                $tags[] = [
                    'tag_id' => $tag->getTagId(),
                    'name' => $tag->getTagName(),
                    'code' => $tag->getTagSlug()
                ];
            }
            
            return $resultJson->setData([
                'items' => $tags,
                'total_count' => $searchResults->getTotalCount()
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Error loading tags: ' . $e->getMessage());
            
            return $resultJson->setData([
                'error' => true,
                'message' => __('Unable to load tags')
            ]);
        }
    }
}