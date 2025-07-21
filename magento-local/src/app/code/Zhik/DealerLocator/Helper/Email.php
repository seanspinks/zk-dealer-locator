<?php
/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zhik\DealerLocator\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;
use Psr\Log\LoggerInterface;
use Zhik\DealerLocator\Api\Data\LocationInterface;

/**
 * Email helper
 */
class Email extends AbstractHelper
{
    const XML_PATH_SEND_CUSTOMER_NOTIFICATIONS = 'dealerlocator/email/send_customer_notifications';
    const XML_PATH_SEND_ADMIN_NOTIFICATIONS = 'dealerlocator/email/send_admin_notifications';
    const XML_PATH_ADMIN_EMAIL = 'dealerlocator/email/admin_email';
    
    const XML_PATH_ADMIN_NEW_SUBMISSION_TEMPLATE = 'dealerlocator/email/admin_new_submission_template';
    const XML_PATH_CUSTOMER_SUBMISSION_CONFIRMATION_TEMPLATE = 'dealerlocator/email/customer_submission_confirmation_template';
    const XML_PATH_CUSTOMER_LOCATION_APPROVED_TEMPLATE = 'dealerlocator/email/customer_location_approved_template';
    const XML_PATH_CUSTOMER_LOCATION_REJECTED_TEMPLATE = 'dealerlocator/email/customer_location_rejected_template';
    
    const EMAIL_TEMPLATE_SUBMISSION_CONFIRMATION = 'dealerlocator_email_customer_submission_confirmation';
    const EMAIL_TEMPLATE_LOCATION_APPROVED = 'dealerlocator_email_customer_location_approved';
    const EMAIL_TEMPLATE_LOCATION_REJECTED = 'dealerlocator_email_customer_location_rejected';
    const EMAIL_TEMPLATE_ADMIN_NEW_SUBMISSION = 'dealerlocator_email_admin_new_submission';

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param UserFactory $userFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        UserFactory $userFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->userFactory = $userFactory;
        $this->logger = $logger;
    }

    /**
     * Send submission confirmation email to customer
     *
     * @param LocationInterface $location
     * @return void
     */
    public function sendSubmissionConfirmation(LocationInterface $location): void
    {
        if (!$this->isCustomerNotificationEnabled()) {
            return;
        }

        try {
            $customer = $this->customerRepository->getById($location->getCustomerId());
            $store = $this->storeManager->getStore();

            $templateVars = [
                'location' => $location,
                'customer' => $customer,
                'store' => $store
            ];

            $templateId = $this->scopeConfig->getValue(
                self::XML_PATH_CUSTOMER_SUBMISSION_CONFIRMATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            ) ?: self::EMAIL_TEMPLATE_SUBMISSION_CONFIRMATION;
            
            $this->sendEmail(
                $templateId,
                $customer->getEmail(),
                $customer->getFirstname() . ' ' . $customer->getLastname(),
                $templateVars,
                (int)$store->getId()
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send submission confirmation email: ' . $e->getMessage());
        }
    }

    /**
     * Send location approved email to customer
     *
     * @param LocationInterface $location
     * @return void
     */
    public function sendLocationApproved(LocationInterface $location): void
    {
        if (!$this->isCustomerNotificationEnabled()) {
            return;
        }

        try {
            $customer = $this->customerRepository->getById($location->getCustomerId());
            $store = $this->storeManager->getStore();

            $templateVars = [
                'location' => $location,
                'customer' => $customer,
                'store' => $store
            ];

            $templateId = $this->scopeConfig->getValue(
                self::XML_PATH_CUSTOMER_LOCATION_APPROVED_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            ) ?: self::EMAIL_TEMPLATE_LOCATION_APPROVED;
            
            $this->sendEmail(
                $templateId,
                $customer->getEmail(),
                $customer->getFirstname() . ' ' . $customer->getLastname(),
                $templateVars,
                (int)$store->getId()
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send location approved email: ' . $e->getMessage());
        }
    }

    /**
     * Send location rejected email to customer
     *
     * @param LocationInterface $location
     * @return void
     */
    public function sendLocationRejected(LocationInterface $location): void
    {
        if (!$this->isCustomerNotificationEnabled()) {
            return;
        }

        try {
            $customer = $this->customerRepository->getById($location->getCustomerId());
            $store = $this->storeManager->getStore();

            $templateVars = [
                'location' => $location,
                'customer' => $customer,
                'store' => $store
            ];

            $templateId = $this->scopeConfig->getValue(
                self::XML_PATH_CUSTOMER_LOCATION_REJECTED_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            ) ?: self::EMAIL_TEMPLATE_LOCATION_REJECTED;
            
            $this->sendEmail(
                $templateId,
                $customer->getEmail(),
                $customer->getFirstname() . ' ' . $customer->getLastname(),
                $templateVars,
                (int)$store->getId()
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send location rejected email: ' . $e->getMessage());
        }
    }

    /**
     * Send new submission notification to admin
     *
     * @param LocationInterface $location
     * @return void
     */
    public function sendAdminNewSubmission(LocationInterface $location): void
    {
        if (!$this->isAdminNotificationEnabled()) {
            return;
        }

        $adminEmail = $this->getAdminEmail();
        if (!$adminEmail) {
            return;
        }

        try {
            $customer = $this->customerRepository->getById($location->getCustomerId());
            $store = $this->storeManager->getStore();

            $templateVars = [
                'location' => $location,
                'customer' => $customer,
                'store' => $store,
                'admin_url' => $this->_urlBuilder->getUrl(
                    'dealerlocator/location/edit',
                    ['location_id' => $location->getLocationId(), '_secure' => true, '_nosid' => true]
                )
            ];

            $templateId = $this->scopeConfig->getValue(
                self::XML_PATH_ADMIN_NEW_SUBMISSION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            ) ?: self::EMAIL_TEMPLATE_ADMIN_NEW_SUBMISSION;
            
            $this->sendEmail(
                $templateId,
                $adminEmail,
                null,
                $templateVars,
                (int)$store->getId()
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send admin notification email: ' . $e->getMessage());
        }
    }

    /**
     * Send email
     *
     * @param string $templateId
     * @param string $toEmail
     * @param string|null $toName
     * @param array $templateVars
     * @param int|null $storeId
     * @return void
     * @throws LocalizedException
     * @throws MailException
     */
    protected function sendEmail(
        string $templateId,
        string $toEmail,
        ?string $toName,
        array $templateVars,
        ?int $storeId = null
    ): void {
        $this->inlineTranslation->suspend();

        try {
            $storeId = $storeId ?: Store::DEFAULT_STORE_ID;
            
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId
                ])
                ->setTemplateVars($templateVars)
                ->setFromByScope('general')
                ->addTo($toEmail, $toName)
                ->getTransport();

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }

    /**
     * Check if customer notifications are enabled
     *
     * @return bool
     */
    protected function isCustomerNotificationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_CUSTOMER_NOTIFICATIONS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if admin notifications are enabled
     *
     * @return bool
     */
    protected function isAdminNotificationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_ADMIN_NOTIFICATIONS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get admin email address
     *
     * @return string|null
     */
    protected function getAdminEmail(): ?string
    {
        $email = $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
        
        if (!$email) {
            // Fallback to general contact email
            $email = $this->scopeConfig->getValue(
                'trans_email/ident_general/email',
                ScopeInterface::SCOPE_STORE
            );
        }
        
        return $email;
    }
}