<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model\Tag\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Zhik\DealerLocator\Model\ResourceModel\Tag\CollectionFactory;

/**
 * Tag options source model
 */
class Options implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $tagCollectionFactory;

    /**
     * @param CollectionFactory $tagCollectionFactory
     */
    public function __construct(
        CollectionFactory $tagCollectionFactory
    ) {
        $this->tagCollectionFactory = $tagCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        
        $collection = $this->tagCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1)
                   ->setOrder('tag_name', 'ASC');
        
        foreach ($collection as $tag) {
            $options[] = [
                'value' => $tag->getTagId(),
                'label' => $tag->getTagName()
            ];
        }
        
        return $options;
    }
}