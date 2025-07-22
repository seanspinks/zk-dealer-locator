<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Api\Data;

/**
 * Dealer Location interface
 * @api
 */
interface LocationInterface
{
    /**
     * Constants for keys of data array
     */
    const LOCATION_ID = 'location_id';
    const CUSTOMER_ID = 'customer_id';
    const PARENT_ID = 'parent_id';
    const NAME = 'name';
    const ADDRESS = 'address';
    const CITY = 'city';
    const STATE = 'state';
    const POSTAL_CODE = 'postal_code';
    const COUNTRY = 'country';
    const LATITUDE = 'latitude';
    const LONGITUDE = 'longitude';
    const PHONE = 'phone';
    const EMAIL = 'email';
    const WEBSITE = 'website';
    const HOURS = 'hours';
    const DESCRIPTION = 'description';
    const IMAGE_URL = 'image_url';
    const STATUS = 'status';
    const REJECTION_REASON = 'rejection_reason';
    const IS_LATEST = 'is_latest';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const APPROVED_AT = 'approved_at';
    const APPROVED_BY = 'approved_by';
    const IP_ADDRESS = 'ip_address';

    /**
     * Status values
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PENDING_DELETION = 'pending_deletion';

    /**
     * Get location ID
     *
     * @return int|null
     */
    public function getLocationId();

    /**
     * Set location ID
     *
     * @param int $locationId
     * @return $this
     */
    public function setLocationId($locationId);

    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get parent ID
     *
     * @return int|null
     */
    public function getParentId();

    /**
     * Set parent ID
     *
     * @param int|null $parentId
     * @return $this
     */
    public function setParentId($parentId);

    /**
     * Get location name
     *
     * @return string
     */
    public function getName();

    /**
     * Set location name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress();

    /**
     * Set address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address);

    /**
     * Get city
     *
     * @return string
     */
    public function getCity();

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Get state
     *
     * @return string|null
     */
    public function getState();

    /**
     * Set state
     *
     * @param string|null $state
     * @return $this
     */
    public function setState($state);

    /**
     * Get postal code
     *
     * @return string
     */
    public function getPostalCode();

    /**
     * Set postal code
     *
     * @param string $postalCode
     * @return $this
     */
    public function setPostalCode($postalCode);

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry();

    /**
     * Set country
     *
     * @param string $country
     * @return $this
     */
    public function setCountry($country);

    /**
     * Get latitude
     *
     * @return float|null
     */
    public function getLatitude();

    /**
     * Set latitude
     *
     * @param float|null $latitude
     * @return $this
     */
    public function setLatitude($latitude);

    /**
     * Get longitude
     *
     * @return float|null
     */
    public function getLongitude();

    /**
     * Set longitude
     *
     * @param float|null $longitude
     * @return $this
     */
    public function setLongitude($longitude);

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone();

    /**
     * Set phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get website
     *
     * @return string|null
     */
    public function getWebsite();

    /**
     * Set website
     *
     * @param string|null $website
     * @return $this
     */
    public function setWebsite($website);

    /**
     * Get hours
     *
     * @return string|null
     */
    public function getHours();

    /**
     * Set hours
     *
     * @param string|null $hours
     * @return $this
     */
    public function setHours($hours);

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
     * Get image URL
     *
     * @return string|null
     */
    public function getImageUrl();

    /**
     * Set image URL
     *
     * @param string|null $imageUrl
     * @return $this
     */
    public function setImageUrl($imageUrl);

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get rejection reason
     *
     * @return string|null
     */
    public function getRejectionReason();

    /**
     * Set rejection reason
     *
     * @param string|null $rejectionReason
     * @return $this
     */
    public function setRejectionReason($rejectionReason);

    /**
     * Get is latest
     *
     * @return bool
     */
    public function getIsLatest();

    /**
     * Set is latest
     *
     * @param bool $isLatest
     * @return $this
     */
    public function setIsLatest($isLatest);

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

    /**
     * Get approved at
     *
     * @return string|null
     */
    public function getApprovedAt();

    /**
     * Set approved at
     *
     * @param string|null $approvedAt
     * @return $this
     */
    public function setApprovedAt($approvedAt);

    /**
     * Get approved by
     *
     * @return int|null
     */
    public function getApprovedBy();

    /**
     * Set approved by
     *
     * @param int|null $approvedBy
     * @return $this
     */
    public function setApprovedBy($approvedBy);

    /**
     * Get IP address
     *
     * @return string|null
     */
    public function getIpAddress();

    /**
     * Set IP address
     *
     * @param string|null $ipAddress
     * @return $this
     */
    public function setIpAddress($ipAddress);
}