<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Block\Adminhtml\Config;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * API Key configuration block
 */
class ApiKey extends Template
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * Get Google Maps API key
     *
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        $apiKey = $this->scopeConfig->getValue(
            'dealerlocator/google_maps/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        if ($apiKey) {
            $apiKey = $this->encryptor->decrypt($apiKey);
        }
        
        return $apiKey;
    }
}