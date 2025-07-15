<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zhik\DealerLocator\Api\Data\TagInterfaceFactory;
use Zhik\DealerLocator\Api\TagRepositoryInterface;

/**
 * Add sample dealer tags
 */
class AddSampleTags implements DataPatchInterface
{
    /**
     * @var TagInterfaceFactory
     */
    private $tagFactory;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @param TagInterfaceFactory $tagFactory
     * @param TagRepositoryInterface $tagRepository
     */
    public function __construct(
        TagInterfaceFactory $tagFactory,
        TagRepositoryInterface $tagRepository
    ) {
        $this->tagFactory = $tagFactory;
        $this->tagRepository = $tagRepository;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $tags = [
            [
                'tag_name' => 'Retailer',
                'tag_slug' => 'retailer',
                'tag_color' => '#5cb85c',
                'description' => 'Retail store location',
                'sort_order' => 10
            ],
            [
                'tag_name' => 'Distributor',
                'tag_slug' => 'distributor',
                'tag_color' => '#5bc0de',
                'description' => 'Distribution center',
                'sort_order' => 20
            ],
            [
                'tag_name' => 'Service Center',
                'tag_slug' => 'service-center',
                'tag_color' => '#f0ad4e',
                'description' => 'Service and repair center',
                'sort_order' => 30
            ],
            [
                'tag_name' => 'Showroom',
                'tag_slug' => 'showroom',
                'tag_color' => '#d9534f',
                'description' => 'Product showroom',
                'sort_order' => 40
            ]
        ];

        foreach ($tags as $tagData) {
            try {
                $tag = $this->tagFactory->create();
                $tag->setTagName($tagData['tag_name']);
                $tag->setTagSlug($tagData['tag_slug']);
                $tag->setTagColor($tagData['tag_color']);
                $tag->setDescription($tagData['description']);
                $tag->setSortOrder($tagData['sort_order']);
                $tag->setIsActive(true);
                
                $this->tagRepository->save($tag);
            } catch (\Exception $e) {
                // Tag might already exist, skip
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}