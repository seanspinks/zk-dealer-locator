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
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

/**
 * Edit location controller
 */
class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::locations_save';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var LocationRepositoryInterface
     */
    private $locationRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param LocationRepositoryInterface $locationRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LocationRepositoryInterface $locationRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->locationRepository = $locationRepository;
    }

    /**
     * Edit action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $locationId = (int)$this->getRequest()->getParam('location_id');
        
        if ($locationId) {
            try {
                $location = $this->locationRepository->getById($locationId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This location no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('*/*/');
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Zhik_DealerLocator::locations');
        $resultPage->addBreadcrumb(__('Dealer Locator'), __('Dealer Locator'));
        $resultPage->addBreadcrumb(__('Locations'), __('Locations'));
        $resultPage->addBreadcrumb(
            $locationId ? __('Edit Location') : __('New Location'),
            $locationId ? __('Edit Location') : __('New Location')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Locations'));
        
        if ($locationId) {
            $resultPage->getConfig()->getTitle()->prepend($location->getName());
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Location'));
        }

        return $resultPage;
    }
}