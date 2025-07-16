<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Get Google Maps API key for admin
 */
class Apikey extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Zhik_DealerLocator::locations';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

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
     * @param JsonFactory $resultJsonFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            $apiKey = $this->scopeConfig->getValue(
                'dealerlocator/google_maps/api_key',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            
            if ($apiKey) {
                $apiKey = $this->encryptor->decrypt($apiKey);
            }
            
            return $resultJson->setData([
                'api_key' => $apiKey ?: ''
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }
}