<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Zhik\DealerLocator\Api\TagRepositoryInterface;

/**
 * Delete tag controller
 */
class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::tags';

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @param Context $context
     * @param TagRepositoryInterface $tagRepository
     */
    public function __construct(
        Context $context,
        TagRepositoryInterface $tagRepository
    ) {
        parent::__construct($context);
        $this->tagRepository = $tagRepository;
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
        
        $tagId = (int)$this->getRequest()->getParam('tag_id');
        if ($tagId) {
            try {
                $this->tagRepository->deleteById($tagId);
                $this->messageManager->addSuccessMessage(__('You deleted the tag.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tagId]);
            }
        }
        
        $this->messageManager->addErrorMessage(__('We can\'t find a tag to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}