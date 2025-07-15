<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model\ResourceModel\Location;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Location collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'location_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'zhik_dealer_location_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'location_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Zhik\DealerLocator\Model\Location::class,
            \Zhik\DealerLocator\Model\ResourceModel\Location::class
        );
    }

    /**
     * Add customer filter
     *
     * @param int $customerId
     * @return $this
     */
    public function addCustomerFilter($customerId)
    {
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }

    /**
     * Add status filter
     *
     * @param string|array $status
     * @return $this
     */
    public function addStatusFilter($status)
    {
        $this->addFieldToFilter('status', $status);
        return $this;
    }

    /**
     * Add is latest filter
     *
     * @param bool $isLatest
     * @return $this
     */
    public function addIsLatestFilter($isLatest = true)
    {
        $this->addFieldToFilter('is_latest', $isLatest ? 1 : 0);
        return $this;
    }

    /**
     * Join tags
     *
     * @return $this
     */
    public function joinTags()
    {
        $this->getSelect()->joinLeft(
            ['location_tags' => $this->getTable('zhik_dealer_location_tags')],
            'main_table.location_id = location_tags.location_id',
            []
        )->joinLeft(
            ['tags' => $this->getTable('zhik_dealer_tags')],
            'location_tags.tag_id = tags.tag_id',
            ['tag_names' => new \Zend_Db_Expr('GROUP_CONCAT(tags.tag_name SEPARATOR ", ")')]
        )->group('main_table.location_id');

        return $this;
    }

    /**
     * Add tag filter
     *
     * @param int|array $tagIds
     * @return $this
     */
    public function addTagFilter($tagIds)
    {
        if (!is_array($tagIds)) {
            $tagIds = [$tagIds];
        }

        $this->getSelect()->joinInner(
            ['location_tags' => $this->getTable('zhik_dealer_location_tags')],
            'main_table.location_id = location_tags.location_id',
            []
        )->where('location_tags.tag_id IN (?)', $tagIds)
        ->group('main_table.location_id');

        return $this;
    }

    /**
     * Add distance filter
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $radius
     * @return $this
     */
    public function addDistanceFilter($latitude, $longitude, $radius)
    {
        $this->getSelect()->where(
            new \Zend_Db_Expr(
                sprintf(
                    '(6371 * acos(cos(radians(%f)) * cos(radians(latitude)) * cos(radians(longitude) - radians(%f)) + sin(radians(%f)) * sin(radians(latitude)))) <= %f',
                    $latitude,
                    $longitude,
                    $latitude,
                    $radius
                )
            )
        );

        return $this;
    }
}