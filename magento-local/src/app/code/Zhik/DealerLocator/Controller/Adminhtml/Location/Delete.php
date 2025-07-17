<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Adminhtml\Location;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

/**
 * Delete location controller
 */
class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::locations_delete';

    /**
     * @var LocationRepositoryInterface
     */
    private $locationRepository;

    /**
     * @param Context $context
     * @param LocationRepositoryInterface $locationRepository
     */
    public function __construct(
        Context $context,
        LocationRepositoryInterface $locationRepository
    ) {
        parent::__construct($context);
        $this->locationRepository = $locationRepository;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        
        $locationId = (int)$this->getRequest()->getParam('location_id');
        if ($locationId) {
            try {
                $this->locationRepository->deleteById($locationId);
                $this->messageManager->addSuccessMessage(__('You deleted the location.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['location_id' => $locationId]);
            }
        }
        
        $this->messageManager->addErrorMessage(__('We can\'t find a location to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}