<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Zhik\DealerLocator\Api\TagRepositoryInterface;

/**
 * Inline edit controller
 */
class InlineEdit extends Action
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
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @param Context $context
     * @param TagRepositoryInterface $tagRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        TagRepositoryInterface $tagRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->tagRepository = $tagRepository;
        $this->jsonFactory = $jsonFactory;
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
                foreach (array_keys($postItems) as $tagId) {
                    /** @var \Zhik\DealerLocator\Model\Tag $tag */
                    try {
                        $tag = $this->tagRepository->getById($tagId);
                        $tagData = $postItems[$tagId];
                        
                        // Handle tag_name field specifically
                        if (isset($tagData['tag_name'])) {
                            $tag->setTagName($tagData['tag_name']);
                        }
                        
                        // Set other fields
                        foreach ($tagData as $key => $value) {
                            if ($key !== 'tag_name') {
                                $tag->setData($key, $value);
                            }
                        }
                        
                        $this->tagRepository->save($tag);
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithTagId(
                            $tag,
                            __($e->getMessage())
                        );
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

    /**
     * Add tag id to error message
     *
     * @param \Zhik\DealerLocator\Api\Data\TagInterface $tag
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithTagId(\Zhik\DealerLocator\Api\Data\TagInterface $tag, $errorText)
    {
        return '[Tag ID: ' . $tag->getTagId() . '] ' . $errorText;
    }
}