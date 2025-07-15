<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Model;

use Magento\Framework\Model\AbstractModel;
use Zhik\DealerLocator\Api\Data\LocationInterface;

/**
 * Dealer Location model
 */
class Location extends AbstractModel implements LocationInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'zhik_dealer_location';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Zhik\DealerLocator\Model\ResourceModel\Location::class);
    }

    /**
     * @inheritdoc
     */
    public function getLocationId()
    {
        return $this->getData(self::LOCATION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setLocationId($locationId)
    {
        return $this->setData(self::LOCATION_ID, $locationId);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @inheritdoc
     */
    public function setAddress($address)
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * @inheritdoc
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        return $this->getData(self::STATE);
    }

    /**
     * @inheritdoc
     */
    public function setState($state)
    {
        return $this->setData(self::STATE, $state);
    }

    /**
     * @inheritdoc
     */
    public function getPostalCode()
    {
        return $this->getData(self::POSTAL_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setPostalCode($postalCode)
    {
        return $this->setData(self::POSTAL_CODE, $postalCode);
    }

    /**
     * @inheritdoc
     */
    public function getCountry()
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * @inheritdoc
     */
    public function setCountry($country)
    {
        return $this->setData(self::COUNTRY, $country);
    }

    /**
     * @inheritdoc
     */
    public function getLatitude()
    {
        return $this->getData(self::LATITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLatitude($latitude)
    {
        return $this->setData(self::LATITUDE, $latitude);
    }

    /**
     * @inheritdoc
     */
    public function getLongitude()
    {
        return $this->getData(self::LONGITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLongitude($longitude)
    {
        return $this->setData(self::LONGITUDE, $longitude);
    }

    /**
     * @inheritdoc
     */
    public function getPhone()
    {
        return $this->getData(self::PHONE);
    }

    /**
     * @inheritdoc
     */
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritdoc
     */
    public function getWebsite()
    {
        return $this->getData(self::WEBSITE);
    }

    /**
     * @inheritdoc
     */
    public function setWebsite($website)
    {
        return $this->setData(self::WEBSITE, $website);
    }

    /**
     * @inheritdoc
     */
    public function getHours()
    {
        return $this->getData(self::HOURS);
    }

    /**
     * @inheritdoc
     */
    public function setHours($hours)
    {
        return $this->setData(self::HOURS, $hours);
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
    public function getImageUrl()
    {
        return $this->getData(self::IMAGE_URL);
    }

    /**
     * @inheritdoc
     */
    public function setImageUrl($imageUrl)
    {
        return $this->setData(self::IMAGE_URL, $imageUrl);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getRejectionReason()
    {
        return $this->getData(self::REJECTION_REASON);
    }

    /**
     * @inheritdoc
     */
    public function setRejectionReason($rejectionReason)
    {
        return $this->setData(self::REJECTION_REASON, $rejectionReason);
    }

    /**
     * @inheritdoc
     */
    public function getIsLatest()
    {
        return (bool)$this->getData(self::IS_LATEST);
    }

    /**
     * @inheritdoc
     */
    public function setIsLatest($isLatest)
    {
        return $this->setData(self::IS_LATEST, $isLatest);
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
     * @inheritdoc
     */
    public function getApprovedAt()
    {
        return $this->getData(self::APPROVED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setApprovedAt($approvedAt)
    {
        return $this->setData(self::APPROVED_AT, $approvedAt);
    }

    /**
     * @inheritdoc
     */
    public function getApprovedBy()
    {
        return $this->getData(self::APPROVED_BY);
    }

    /**
     * @inheritdoc
     */
    public function setApprovedBy($approvedBy)
    {
        return $this->setData(self::APPROVED_BY, $approvedBy);
    }

    /**
     * @inheritdoc
     */
    public function getIpAddress()
    {
        return $this->getData(self::IP_ADDRESS);
    }

    /**
     * @inheritdoc
     */
    public function setIpAddress($ipAddress)
    {
        return $this->setData(self::IP_ADDRESS, $ipAddress);
    }
}