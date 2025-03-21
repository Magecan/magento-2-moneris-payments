<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class GeneralDataBuilder implements BuilderInterface
{
    /**
     * Builds the request data for payment processing.
     *
     * @param array $buildSubject An array containing the data needed for the request.
     * @return array The request data array, which is identical to the input subject data.
     */
    public function build(array $buildSubject)
    {
        return $buildSubject;
    }
}
