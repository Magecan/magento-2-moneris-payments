<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Gateway\Helper;

use InvalidArgumentException;
use Magento\Quote\Model\Quote;
use Magento\Payment\Gateway\Helper;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class SubjectReader
{
    use \Magento\Payment\Helper\Formatter;

    /**
     * Reads response object from subject
     *
     * @param array $subject
     * @return object
     */
    public function readResponseObject(array $subject)
    {
        $response = Helper\SubjectReader::readResponse($subject);
        if (!isset($response['mpg_response']) || !is_object($response['mpg_response'])) {
            throw new InvalidArgumentException('Response object does not exist');
        }

        return $response['mpg_response'];
    }

    /**
     * Reads mpgResponse from subject
     *
     * @param array $subject
     * @return MpgResponse
     */
    public function readMpgResponse(array $subject)
    {
        if (!isset($subject['mpg_response']) || !is_object($subject['mpg_response'])) {
            throw new InvalidArgumentException('MpgResponse object does not exist');
        }

        return $subject['mpg_response'];
    }

    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject): PaymentDataObjectInterface
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return  $this->formatPrice(Helper\SubjectReader::readAmount($subject));
    }

    /**
     * Reads customer id from subject
     *
     * @param array $subject
     * @return int
     */
    public function readCustomerId(array $subject): int
    {
        if (!isset($subject['customer_id'])) {
            throw new InvalidArgumentException('The "customerId" field does not exists');
        }

        return (int) $subject['customer_id'];
    }

    /**
     * Reads billing address from subject
     *
     * @param array $subject
     * @return array | null
     */
    public function readBillingAddress(array $subject)
    {
        if (empty($subject['billingAddress'])) {
            return null;
        }

        return $subject['billingAddress'];
    }
}
