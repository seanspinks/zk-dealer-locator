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
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Zhik\DealerLocator\Api\TagRepositoryInterface;

/**
 * Edit tag controller
 */
class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::tags';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param TagRepositoryInterface $tagRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TagRepositoryInterface $tagRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Edit action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $tagId = (int)$this->getRequest()->getParam('tag_id');
        
        if ($tagId) {
            try {
                $tag = $this->tagRepository->getById($tagId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This tag no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('*/*/');
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Zhik_DealerLocator::tags');
        $resultPage->addBreadcrumb(__('Dealer Locator'), __('Dealer Locator'));
        $resultPage->addBreadcrumb(__('Tags'), __('Tags'));
        $resultPage->addBreadcrumb(
            $tagId ? __('Edit Tag') : __('New Tag'),
            $tagId ? __('Edit Tag') : __('New Tag')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Tags'));
        
        if ($tagId) {
            $resultPage->getConfig()->getTitle()->prepend($tag->getName());
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Tag'));
        }

        return $resultPage;
    }
}