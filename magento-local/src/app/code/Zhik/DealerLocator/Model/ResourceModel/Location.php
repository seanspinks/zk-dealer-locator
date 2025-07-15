<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Location resource model
 */
class Location extends AbstractDb
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $date,
        $connectionName = null
    ) {
        $this->date = $date;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('zhik_dealer_locations', 'location_id');
    }

    /**
     * Process data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCreatedAt()) {
            $object->setCreatedAt($this->date->gmtDate());
        }

        $object->setUpdatedAt($this->date->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Get location tags
     *
     * @param int $locationId
     * @return array
     */
    public function getLocationTags($locationId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('zhik_dealer_location_tags'), ['tag_id'])
            ->where('location_id = ?', $locationId);

        return $connection->fetchCol($select);
    }

    /**
     * Save location tags
     *
     * @param int $locationId
     * @param array $tagIds
     * @return $this
     */
    public function saveLocationTags($locationId, array $tagIds)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('zhik_dealer_location_tags');

        // Delete existing tags
        $connection->delete($table, ['location_id = ?' => $locationId]);

        // Insert new tags
        if (!empty($tagIds)) {
            $data = [];
            foreach ($tagIds as $tagId) {
                $data[] = [
                    'location_id' => $locationId,
                    'tag_id' => $tagId
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $this;
    }
}