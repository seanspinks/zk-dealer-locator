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
use Magento\Ui\Component\MassAction\Filter;
use Zhik\DealerLocator\Model\ResourceModel\Location\CollectionFactory;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

/**
 * Mass delete locations controller
 */
class MassDelete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::locations_delete';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var LocationRepositoryInterface
     */
    private $locationRepository;

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
     * Execute mass delete
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $location) {
            try {
                $this->locationRepository->deleteById((int)$location->getLocationId());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Location %1 could not be deleted: %2', $location->getName(), $e->getMessage())
                );
            }
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $collectionSize)
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}