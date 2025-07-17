<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Block\Customer\Location;

use Magento\Customer\Model\Session;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Psr\Log\LoggerInterface;
use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Api\TagRepositoryInterface;
use Zhik\DealerLocator\Api\Data\LocationInterfaceFactory;

/**
 * Location form block
 */
class Form extends Template
{
    /**
     * @var LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * @var TagRepositoryInterface
     */
    protected $tagRepository;

    /**
     * @var LocationInterfaceFactory
     */
    protected $locationFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CountryCollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Zhik\DealerLocator\Api\Data\LocationInterface|null
     */
    protected $location = null;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param LocationRepositoryInterface $locationRepository
     * @param TagRepositoryInterface $tagRepository
     * @param LocationInterfaceFactory $locationFactory
     * @param Session $customerSession
     * @param CountryCollectionFactory $countryCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        LocationRepositoryInterface $locationRepository,
        TagRepositoryInterface $tagRepository,
        LocationInterfaceFactory $locationFactory,
        Session $customerSession,
        CountryCollectionFactory $countryCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->locationRepository = $locationRepository;
        $this->tagRepository = $tagRepository;
        $this->locationFactory = $locationFactory;
        $this->customerSession = $customerSession;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
    }

    /**
     * Get location
     *
     * @return \Zhik\DealerLocator\Api\Data\LocationInterface
     */
    public function getLocation()
    {
        if ($this->location === null) {
            $locationId = $this->getRequest()->getParam('id');
            if ($locationId) {
                try {
                    $location = $this->locationRepository->getById((int)$locationId);
                    // Verify ownership
                    if ($location->getCustomerId() == $this->customerSession->getCustomerId()) {
                        $this->location = $location;
                    }
                } catch (\Exception $e) {
                    $this->logger->debug(
                        'Failed to load location in customer form',
                        [
                            'location_id' => $locationId,
                            'customer_id' => $this->customerSession->getCustomerId(),
                            'exception' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]
                    );
                    // Location not found or access denied - will create empty location below
                }
            }
            
            if ($this->location === null) {
                $this->location = $this->locationFactory->create();
            }
        }
        return $this->location;
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * Get back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * Get available tags
     *
     * @return array
     */
    public function getAvailableTags()
    {
        return $this->tagRepository->getActiveTags();
    }

    /**
     * Get countries collection
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getCountries()
    {
        return $this->countryCollectionFactory->create()->loadByStore();
    }

    /**
     * Get default country
     *
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->scopeConfig->getValue('general/country/default');
    }

    /**
     * Get Google Maps API key
     *
     * @return string
     */
    public function getGoogleMapsApiKey()
    {
        $apiKey = $this->scopeConfig->getValue('dealerlocator/google_maps/api_key');
        return $apiKey ?: '';
    }

    /**
     * Check if field is enabled
     *
     * @param string $field
     * @return bool
     */
    public function isFieldEnabled($field)
    {
        return (bool)$this->scopeConfig->getValue('dealerlocator/fields/' . $field . '_enabled');
    }

    /**
     * Check if field is required
     *
     * @param string $field
     * @return bool
     */
    public function isFieldRequired($field)
    {
        return (bool)$this->scopeConfig->getValue('dealerlocator/fields/' . $field . '_required');
    }

    /**
     * Get form data
     *
     * @return array
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $formData = $this->customerSession->getLocationFormData(true);
            $data = new \Magento\Framework\DataObject();
            if ($formData) {
                $data->addData($formData);
            } else {
                $location = $this->getLocation();
                if ($location->getLocationId()) {
                    $data->addData($location->getData());
                }
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }

    /**
     * Check if location has selected tag
     *
     * @param int $tagId
     * @return bool
     */
    public function hasTag($tagId)
    {
        $tagIds = $this->getLocation()->getData('tag_ids') ?: [];
        return in_array($tagId, $tagIds);
    }
}