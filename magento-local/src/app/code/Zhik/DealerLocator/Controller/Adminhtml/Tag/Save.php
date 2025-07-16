<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Zhik\DealerLocator\Api\Data\TagInterfaceFactory;
use Zhik\DealerLocator\Api\TagRepositoryInterface;

/**
 * Save tag controller
 */
class Save extends Action implements HttpPostActionInterface
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
     * @var TagInterfaceFactory
     */
    private $tagFactory;

    /**
     * @param Context $context
     * @param TagRepositoryInterface $tagRepository
     * @param TagInterfaceFactory $tagFactory
     */
    public function __construct(
        Context $context,
        TagRepositoryInterface $tagRepository,
        TagInterfaceFactory $tagFactory
    ) {
        parent::__construct($context);
        $this->tagRepository = $tagRepository;
        $this->tagFactory = $tagFactory;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $tagId = !empty($data['tag_id']) ? (int)$data['tag_id'] : null;
            
            if ($tagId) {
                $tag = $this->tagRepository->getById($tagId);
            } else {
                $tag = $this->tagFactory->create();
            }

            // Set tag data
            $tagName = $data['tag_name'] ?? $data['name'] ?? '';
            $tag->setTagName($tagName);
            
            // Generate slug from tag name if not provided
            if (empty($data['tag_slug']) && $tagName) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $tagName), '-'));
                $tag->setTagSlug($slug);
            } elseif (!empty($data['tag_slug'])) {
                $tag->setTagSlug($data['tag_slug']);
            }
            
            $tag->setDescription($data['description'] ?? '');
            $tag->setIsActive(isset($data['is_active']) ? (int)$data['is_active'] : 1);
            
            $this->tagRepository->save($tag);

            $this->messageManager->addSuccessMessage(__('You saved the tag.'));
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
            
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tag->getTagId()]);
            }
            
            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the tag.'));
        }

        $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData($data);
        return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tagId]);
    }
}