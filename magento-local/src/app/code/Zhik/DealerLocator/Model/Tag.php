<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model;

use Magento\Framework\Model\AbstractModel;
use Zhik\DealerLocator\Api\Data\TagInterface;

/**
 * Dealer Tag model
 */
class Tag extends AbstractModel implements TagInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'zhik_dealer_tag';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Zhik\DealerLocator\Model\ResourceModel\Tag::class);
    }

    /**
     * @inheritdoc
     */
    public function getTagId()
    {
        return $this->getData(self::TAG_ID);
    }

    /**
     * @inheritdoc
     */
    public function setTagId($tagId)
    {
        return $this->setData(self::TAG_ID, $tagId);
    }

    /**
     * @inheritdoc
     */
    public function getTagName()
    {
        return $this->getData(self::TAG_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setTagName($tagName)
    {
        return $this->setData(self::TAG_NAME, $tagName);
    }

    /**
     * @inheritdoc
     */
    public function getTagSlug()
    {
        return $this->getData(self::TAG_SLUG);
    }

    /**
     * @inheritdoc
     */
    public function setTagSlug($tagSlug)
    {
        return $this->setData(self::TAG_SLUG, $tagSlug);
    }

    /**
     * @inheritdoc
     */
    public function getTagColor()
    {
        return $this->getData(self::TAG_COLOR);
    }

    /**
     * @inheritdoc
     */
    public function setTagColor($tagColor)
    {
        return $this->setData(self::TAG_COLOR, $tagColor);
    }

    /**
     * @inheritdoc
     */
    public function getTagIcon()
    {
        return $this->getData(self::TAG_ICON);
    }

    /**
     * @inheritdoc
     */
    public function setTagIcon($tagIcon)
    {
        return $this->setData(self::TAG_ICON, $tagIcon);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder()
    {
        return (int)$this->getData(self::SORT_ORDER);
    }

    /**
     * @inheritdoc
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * @inheritdoc
     */
    public function getIsActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Prepare tag slug
     *
     * @return AbstractModel
     */
    public function beforeSave()
    {
        if (!$this->getTagSlug() && $this->getTagName()) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->getTagName())));
            $this->setTagSlug($slug);
        }
        
        return parent::beforeSave();
    }
}