<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model\ResourceModel\Tag;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Tag collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'tag_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'zhik_dealer_tag_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'tag_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Zhik\DealerLocator\Model\Tag::class,
            \Zhik\DealerLocator\Model\ResourceModel\Tag::class
        );
    }

    /**
     * Add active filter
     *
     * @return $this
     */
    public function addActiveFilter()
    {
        $this->addFieldToFilter('is_active', 1);
        return $this;
    }

    /**
     * Set sort order by sort_order field
     *
     * @return $this
     */
    public function setSortOrder()
    {
        $this->setOrder('sort_order', self::SORT_ORDER_ASC);
        return $this;
    }

    /**
     * Join usage count
     *
     * @return $this
     */
    public function joinUsageCount()
    {
        $this->getSelect()->joinLeft(
            ['location_tags' => $this->getTable('zhik_dealer_location_tags')],
            'main_table.tag_id = location_tags.tag_id',
            ['usage_count' => new \Zend_Db_Expr('COUNT(location_tags.location_id)')]
        )->group('main_table.tag_id');

        return $this;
    }
}