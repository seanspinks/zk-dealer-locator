<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Api\Data;

/**
 * Dealer Tag interface
 * @api
 */
interface TagInterface
{
    /**
     * Constants for keys of data array
     */
    const TAG_ID = 'tag_id';
    const TAG_NAME = 'tag_name';
    const TAG_SLUG = 'tag_slug';
    const TAG_COLOR = 'tag_color';
    const TAG_ICON = 'tag_icon';
    const DESCRIPTION = 'description';
    const SORT_ORDER = 'sort_order';
    const IS_ACTIVE = 'is_active';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get tag ID
     *
     * @return int|null
     */
    public function getTagId();

    /**
     * Set tag ID
     *
     * @param int $tagId
     * @return $this
     */
    public function setTagId($tagId);

    /**
     * Get tag name
     *
     * @return string
     */
    public function getTagName();

    /**
     * Set tag name
     *
     * @param string $tagName
     * @return $this
     */
    public function setTagName($tagName);

    /**
     * Get tag slug
     *
     * @return string
     */
    public function getTagSlug();

    /**
     * Set tag slug
     *
     * @param string $tagSlug
     * @return $this
     */
    public function setTagSlug($tagSlug);

    /**
     * Get tag color
     *
     * @return string|null
     */
    public function getTagColor();

    /**
     * Set tag color
     *
     * @param string|null $tagColor
     * @return $this
     */
    public function setTagColor($tagColor);

    /**
     * Get tag icon
     *
     * @return string|null
     */
    public function getTagIcon();

    /**
     * Set tag icon
     *
     * @param string|null $tagIcon
     * @return $this
     */
    public function setTagIcon($tagIcon);

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     *
     * @param string|null $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get is active
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * Set is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}