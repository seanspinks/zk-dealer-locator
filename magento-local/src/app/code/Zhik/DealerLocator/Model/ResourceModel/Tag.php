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
 * Tag resource model
 */
class Tag extends AbstractDb
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
        $this->_init('zhik_dealer_tags', 'tag_id');
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
     * Get tag usage count
     *
     * @param int $tagId
     * @return int
     */
    public function getUsageCount($tagId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('zhik_dealer_location_tags'), ['count' => 'COUNT(*)'])
            ->where('tag_id = ?', $tagId);

        return (int)$connection->fetchOne($select);
    }

    /**
     * Get locations using this tag
     *
     * @param int $tagId
     * @return array
     */
    public function getLocationIds($tagId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('zhik_dealer_location_tags'), ['location_id'])
            ->where('tag_id = ?', $tagId);

        return $connection->fetchCol($select);
    }
}