<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Gateway\Response;

/**
 * Payment Details Handler
 */
class AuthorizeHandler extends AbstractHandler
{
/**
 * Handles authorization response and updates payment information.
 *
 * @param array $handlingSubject Context data provided by the payment gateway.
 * @param array $response Response data from the payment gateway, containing payment details.
 * @return void
 */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment(); /** @var OrderPaymentInterface $payment */

        if (isset($response['receipt']['cc']['card_type'])) {
            $payment->setAdditionalInformation(
                'card_type',
                $this->getCreditCardLabel($response['receipt']['cc']['card_type'])
            );
        }
        if (isset($response['receipt']['cc']['first6last4'])) {
            $payment->setAdditionalInformation(
                'card_num',
                'xxxx-' . substr($response['receipt']['cc']['first6last4'], -4)
            );
        }
        if (isset($response['receipt']['cc']['reference_no'])) {
            $payment->setAdditionalInformation('reference_no', $response['receipt']['cc']['reference_no']);
        }
        if (isset($response['receipt']['cc']['response_code'])) {
            $payment->setAdditionalInformation('response_code', $response['receipt']['cc']['response_code']);
        }
        if (isset($response['receipt']['cc']['transaction_date_time'])) {
            $payment->setAdditionalInformation(
                'transaction_date_time',
                $response['receipt']['cc']['transaction_date_time']
            );
        }
        if (isset($response['receipt']['cc']['wallet_type'])) {
            $payment->setAdditionalInformation('wallet_type', $response['receipt']['cc']['wallet_type']);
        }

        if (isset($response['receipt']['cc']['transaction_no'])) {
            $payment->setTransactionId($response['receipt']['cc']['transaction_no']);
            $payment->setCcTransId($response['receipt']['cc']['transaction_no']);
        }
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
    }

    /**
     * Get type of credit card mapped from Moneris
     *
     * @param string $typeCode
     * @return string
     */
    private function getCreditCardLabel($typeCode)
    {
        $mapper = $this->config->getCctypesMonerisMapper();

        return isset($mapper[$typeCode]['label']) ? $mapper[$typeCode]['label'] : $typeCode;
    }
}
