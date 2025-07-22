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
 * Mass approve deletion action
 */
class MassApproveDeletion extends Action
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
        $deletedCount = 0;

        foreach ($collection as $location) {
            try {
                // Only process locations that are pending deletion
                if ($location->getStatus() === LocationInterface::STATUS_PENDING_DELETION) {
                    $this->locationRepository->deleteById((int)$location->getLocationId());
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Error deleting location %1: %2', $location->getName(), $e->getMessage())
                );
            }
        }

        if ($deletedCount > 0) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 location(s) have been deleted.', $deletedCount)
            );
        }
        
        if ($deletedCount < $collectionSize) {
            $this->messageManager->addNoticeMessage(
                __('%1 location(s) were not pending deletion and were skipped.', $collectionSize - $deletedCount)
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}