<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Adminhtml\Location;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Model\ResourceModel\Location\CollectionFactory;
use Zhik\DealerLocator\Api\Data\LocationInterface;

/**
 * Mass reject deletion action
 */
class MassRejectDeletion extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::location';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param LocationRepositoryInterface $locationRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        LocationRepositoryInterface $locationRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->locationRepository = $locationRepository;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        $rejectedCount = 0;

        foreach ($collection as $location) {
            try {
                // Only process locations that are pending deletion
                if ($location->getStatus() === LocationInterface::STATUS_PENDING_DELETION) {
                    // Change status back to its previous state (approved or pending)
                    // If location was approved before, set it back to approved
                    $newStatus = $location->getApprovedAt() ? 
                        LocationInterface::STATUS_APPROVED : 
                        LocationInterface::STATUS_PENDING;
                    
                    $location->setStatus($newStatus);
                    $this->locationRepository->save($location);
                    $rejectedCount++;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Error rejecting deletion for location %1: %2', $location->getName(), $e->getMessage())
                );
            }
        }

        if ($rejectedCount > 0) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 deletion request(s) have been rejected.', $rejectedCount)
            );
        }
        
        if ($rejectedCount < $collectionSize) {
            $this->messageManager->addNoticeMessage(
                __('%1 location(s) were not pending deletion and were skipped.', $collectionSize - $rejectedCount)
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}