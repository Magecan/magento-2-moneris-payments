<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Gateway\Http\Client;

class TransactionAuthorize extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $paymentDO = $this->subjectReader->readPayment($data); /*@var PaymentDataObjectInterface $paymentDO*/
        $payment = $paymentDO->getPayment();

        $requestData = [
            'store_id' => $this->config->getStoreId(),
            'api_token' => $this->config->getApiToken(),
            'checkout_id' => $this->config->getCheckoutId(),
            'ticket' => $payment->getAdditionalInformation('ticket'),
            'environment' => $this->config->getEnvironment(),
            'action' => 'receipt'
        ];

        return $this->getCurlResponse($requestData);
    }
}
