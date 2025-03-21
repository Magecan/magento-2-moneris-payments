<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class AuthorizeResponseValidator extends CheckoutGeneralResponseValidator
{
    /**
     * Validate data
     *
     * @param DataObject|Object $validationSubject
     * @return bool
     */
    public function validate(array $validationSubject): ResultInterface
    {
        parent::validate($validationSubject);

        $isValid= true;
        $errorMessages = [];
        $errorCodes = [];
        $response = $this->subjectReader->readResponse($validationSubject);

        if ($response['receipt']['result'] != 'a') {
            $isValid = false;
            if (isset($response['receipt']['cc']['response_code'])) {
                $errorCodes[] = $response['receipt']['cc']['response_code'];
            } else {
                $errorMessages[] = 'Transaction has been declined. Please try again later.';
            }
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
