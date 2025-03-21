<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Gateway\Http\Client;

class TransactionPreload extends AbstractTransaction
{
    use \Magento\Payment\Helper\Formatter;

    protected const DATAKEY_LENGTH = 25;
    protected const MAXINUM_TOKEN_NUM = 3;

    /**
     * Locale interface
     *
     * @var \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    protected $localeResolver;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Vault\Model\CustomerTokenManagement
     */
    protected $customerTokenManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * TransactionPreload constructor.
     *
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Vault\Model\CustomerTokenManagement $customerTokenManagement
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Psr\Log\LoggerInterface $psrLoger
     * @param \Magecan\Moneris\Gateway\Config\Config $config
     * @param \Magento\Payment\Model\Method\Logger $paymentLogger
     * @param \Magecan\Moneris\Gateway\Helper\SubjectReader $subjectReader
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Vault\Model\CustomerTokenManagement $customerTokenManagement,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Psr\Log\LoggerInterface $psrLoger,
        \Magecan\Moneris\Gateway\Config\Config $config,
        \Magento\Payment\Model\Method\Logger $paymentLogger,
        \Magecan\Moneris\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->localeResolver = $localeResolver;
        $this->checkoutSession = $checkoutSession;
        $this->customerTokenManagement = $customerTokenManagement;
        $this->quoteRepository = $quoteRepository;
        parent::__construct(
            $psrLoger,
            $config,
            $paymentLogger,
            $subjectReader,
            $curl,
            $serializer
        );
    }

    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $quote = $this->checkoutSession->getQuote();
        $locale = $this->localeResolver->getLocale();
        $billingAddress = $this->subjectReader->readBillingAddress($data);

        $requestData = [
            'store_id' => $this->config->getStoreId(),
            'api_token' => $this->config->getApiToken(),
            'checkout_id' => $this->config->getCheckoutId(),
            'txn_total' => $this->formatPrice($quote->getGrandTotal()),
            'environment' => $this->config->getEnvironment(),
            'action' => 'preload'
        ];

        $quote->setReservedOrderId(null);
        $quote->reserveOrderId();
        $this->quoteRepository->save($quote);
        $requestData['order_no'] = $quote->getReservedOrderId();

        $requestData['cust_id'] = $quote->getCustomer()->getId();

        if (!empty($this->config->getDynamicDescriptor())) {
            $requestData['dynamic_descriptor'] = $this->config->getDynamicDescriptor();
        }

        if (strpos($locale, 'fr') !== false) {
            $requestData['language'] = 'fr';
        }

        /* Billing-related fields are required when sending 3-D Secure authentication transactions,
           or else the authentication process may fail. */
        if (null != $billingAddress) {
            $requestData['billing_details'] = [
                'address_1' => isset($billingAddress['street'][0]) ? $billingAddress['street'][0] : null,
                'address_2' => isset($billingAddress['street'][1]) ? $billingAddress['street'][1] : null,
                'city' => isset($billingAddress['city']) ? $billingAddress['city'] : null,
                'province' => isset($billingAddress['regionCode']) ? $billingAddress['regionCode'] : null,
                'country' => isset($billingAddress['countryId']) ? $billingAddress['countryId'] : null,
                'postal_code' => isset($billingAddress['postcode']) ? $billingAddress['postcode'] : null
            ];
        }

        if ($this->config->isVaultActive()) {
            $requestData['ask_cvv'] = $this->config->isAskCvv() ? 'Y' : 'N';

            $customerSessionTokens = $this->customerTokenManagement->getCustomerSessionTokens();

            $i = 0;
            foreach ($customerSessionTokens as $token) {
                if ($i >= self::MAXINUM_TOKEN_NUM) {
                    break;
                }

                if ($token->getPaymentMethodCode() != \Magecan\Moneris\Model\ConfigProvider::CODE) {
                    continue;
                }

                $token = $token->getGatewayToken();
                $requestData['token'][$i++] = [
                    'data_key' => substr($token, 0, self::DATAKEY_LENGTH),
                    'issuer_id' =>  substr($token, self::DATAKEY_LENGTH)
                ];
            }
        }

        return $this->getCurlResponse($requestData);
    }
}
