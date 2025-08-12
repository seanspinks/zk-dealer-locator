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
use Magento\Ui\Component\MassAction\Filter;
use Zhik\DealerLocator\Model\ResourceModel\Tag\CollectionFactory;
use Zhik\DealerLocator\Api\TagRepositoryInterface;

/**
 * Mass disable tags controller
 */
class MassDisable extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::tags';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param TagRepositoryInterface $tagRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        TagRepositoryInterface $tagRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Execute mass disable
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $tag) {
            try {
                $tag->setIsActive(0);
                $this->tagRepository->save($tag);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Tag %1 could not be disabled: %2', $tag->getName(), $e->getMessage())
                );
            }
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been disabled.', $collectionSize)
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}