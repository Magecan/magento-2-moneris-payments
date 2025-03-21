<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Gateway\Response;

class VaultDetailsHandler extends AbstractHandler
{
    /**
     * @var PaymentTokenInterfaceFactory
     */
    protected $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var Json
     */
    public $serializer;

    /**
     * Constructor
     *
     * @param \Magecan\Moneris\Gateway\Config\Config $config
     * @param \Magecan\Moneris\Gateway\Helper\SubjectReader $subjectReader
     * @param \Magento\Vault\Api\Data\PaymentTokenFactoryInterface $paymentTokenFactory
     * @param \Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        \Magecan\Moneris\Gateway\Config\Config $config,
        \Magecan\Moneris\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Vault\Api\Data\PaymentTokenFactoryInterface $paymentTokenFactory,
        \Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->serializer = $serializer;
            
        parent::__construct($config, $subjectReader);
    }
    
    /**
     * Processes the Moneris gateway response to store the payment token in the vault.
     *
     * @param array $handlingSubject Contains the payment object.
     * @param array $response Response data from the Moneris gateway containing the payment token.
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment(); /** @var OrderPaymentInterface $payment */

        if ($payment->getAdditionalInformation('is_active_payment_token_enabler')
            && isset($response['receipt']['cc']['tokenize'])) {
            try {
                // add vault payment token entity to extension attributes
                $paymentToken = $this->getVaultPaymentToken($response);
                if (null !== $paymentToken) {
                    $extensionAttributes = $this->getExtensionAttributes($payment);
                    $extensionAttributes->setVaultPaymentToken($paymentToken);
                }
            } catch (\Exception $e) {
                $doNothing = true;
            }
        }
    }

    /**
     * Get payment extension attributes
     *
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    public function getExtensionAttributes($payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * Get vault payment token entity
     *
     * @param array $response
     * @return PaymentTokenInterface|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function getVaultPaymentToken($response)
    {
        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create(
            \Magento\Vault\Api\Data\PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD
        );
        $paymentToken->setGatewayToken(
            $response['receipt']['cc']['tokenize']['datakey'] . $response['receipt']['cc']['issuer_id']
        );
        $paymentToken->setExpiresAt(
            '20' . substr($response['receipt']['cc']['expiry_date'], 2, 2)
            . '-' . substr($response['receipt']['cc']['expiry_date'], 0, 2)
        );

        $paymentToken->setTokenDetails($this->convertDetailsToJSON([
            'type' => $this->getCreditCardType($response['receipt']['cc']['card_type']),
            'maskedCC' => substr($response['receipt']['cc']['tokenize']['first4last4'], -4),
            'expirationDate' => substr($response['receipt']['cc']['expiry_date'], 0, 2) . '/20'
                . substr($response['receipt']['cc']['expiry_date'], 2, 2)
        ]));

        return $paymentToken;
    }

    /**
     * Convert payment token details to JSON
     *
     * @param array $details
     * @return string
     */
    public function convertDetailsToJSON($details): string
    {
        $json = $this->serializer->serialize($details);
        return $json ?: '{}';
    }

    /**
     * Convert credit card type in Moneris Recepit response to than used in Magento
     *
     * @param string $responseCardType
     * @return string
     */
    private function getCreditCardType($responseCardType): string
    {
        $mapper = $this->config->getCctypesMonerisMapper();

        return isset($mapper[$responseCardType]['type']) ? $mapper[$responseCardType]['type'] : $responseCardType;
    }
}
