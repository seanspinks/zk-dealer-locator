<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Plugin;

use Zhik\DealerLocator\Api\LocationRepositoryInterface;
use Zhik\DealerLocator\Api\Data\LocationInterface;
use Zhik\DealerLocator\Api\Data\LocationExtensionFactory;
use Zhik\DealerLocator\Api\TagRepositoryInterface;

/**
 * Plugin to add extension attributes to locations
 */
class LocationRepositoryPlugin
{
    /**
     * @var LocationExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @param LocationExtensionFactory $extensionFactory
     * @param TagRepositoryInterface $tagRepository
     */
    public function __construct(
        LocationExtensionFactory $extensionFactory,
        TagRepositoryInterface $tagRepository
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Add extension attributes after loading location
     *
     * @param LocationRepositoryInterface $subject
     * @param LocationInterface $location
     * @return LocationInterface
     */
    public function afterGetById(
        LocationRepositoryInterface $subject,
        LocationInterface $location
    ): LocationInterface {
        return $this->addExtensionAttributes($location);
    }

    /**
     * Add extension attributes after saving location
     *
     * @param LocationRepositoryInterface $subject
     * @param LocationInterface $location
     * @return LocationInterface
     */
    public function afterSave(
        LocationRepositoryInterface $subject,
        LocationInterface $location
    ): LocationInterface {
        return $this->addExtensionAttributes($location);
    }

    /**
     * Add extension attributes to locations in list
     *
     * @param LocationRepositoryInterface $subject
     * @param \Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface $searchResults
     * @return \Zhik\DealerLocator\Api\Data\LocationSearchResultsInterface
     */
    public function afterGetList(
        LocationRepositoryInterface $subject,
        $searchResults
    ) {
        foreach ($searchResults->getItems() as $location) {
            $this->addExtensionAttributes($location);
        }
        return $searchResults;
    }

    /**
     * Add extension attributes to location
     *
     * @param LocationInterface $location
     * @return LocationInterface
     */
    private function addExtensionAttributes(LocationInterface $location): LocationInterface
    {
        $extensionAttributes = $location->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->extensionFactory->create();
        }

        // Load tags if available
        $tagIds = $location->getData('tag_ids');
        if ($tagIds && is_array($tagIds)) {
            $tags = [];
            foreach ($tagIds as $tagId) {
                try {
                    $tags[] = $this->tagRepository->getById((int)$tagId);
                } catch (\Exception $e) {
                    // Skip invalid tag
                }
            }
            $extensionAttributes->setTags($tags);
        }

        // Set distance if available
        $distance = $location->getData('distance');
        if ($distance !== null) {
            $extensionAttributes->setDistance((float)$distance);
        }

        $location->setExtensionAttributes($extensionAttributes);
        return $location;
    }
}