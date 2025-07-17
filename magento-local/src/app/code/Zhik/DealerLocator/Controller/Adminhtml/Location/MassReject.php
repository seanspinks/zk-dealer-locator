<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Adminhtml\Location;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Model\ResourceModel\Location\CollectionFactory;

/**
 * Mass reject locations controller
 */
class MassReject extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::locations_approve';

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
     * @var Session
     */
    private $authSession;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param LocationRepositoryInterface $locationRepository
     * @param Session $authSession
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        LocationRepositoryInterface $locationRepository,
        Session $authSession
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->locationRepository = $locationRepository;
        $this->authSession = $authSession;
    }

    /**
     * Mass reject action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        $adminUserId = (int)$this->authSession->getUser()->getId();
        $reason = $this->getRequest()->getParam('reason', __('Bulk rejection by admin'));
        $rejected = 0;

        foreach ($collection as $location) {
            try {
                $this->locationRepository->reject((int)$location->getLocationId(), (string)$reason, $adminUserId);
                $rejected++;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('An error occurred while rejecting location "%1".', $location->getName())
                );
            }
        }

        if ($rejected) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been rejected.', $rejected)
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}