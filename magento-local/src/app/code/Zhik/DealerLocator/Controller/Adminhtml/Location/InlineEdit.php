<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Adminhtml\Location;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;

/**
 * Inline edit location controller
 */
class InlineEdit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::locations_save';

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var LocationRepositoryInterface
     */
    private $locationRepository;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param LocationRepositoryInterface $locationRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        LocationRepositoryInterface $locationRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->locationRepository = $locationRepository;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $locationId) {
                    try {
                        $location = $this->locationRepository->getById($locationId);
                        $location->setData(array_merge($location->getData(), $postItems[$locationId]));
                        $this->locationRepository->save($location);
                    } catch (\Exception $e) {
                        $messages[] = __('[Location ID: %1] %2', $locationId, $e->getMessage());
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}