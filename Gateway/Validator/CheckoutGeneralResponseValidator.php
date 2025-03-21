<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/* For Moneris Checkout Preload and Receipt request */
class CheckoutGeneralResponseValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * Constructor
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * Validate data
     *
     * @param DataObject|Object $validationSubject
     * @return bool
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $response = $this->subjectReader->readResponse($validationSubject);

        if ($response['success'] != 'true') {
            $errorMessage = '';
            
            if (!empty($response['error'])) {
                array_walk_recursive(
                    $response['error'],
                    function ($value, $key) use (&$errorMessage) {
                        $errorMessage .= ucwords(str_replace('_', ' ', $key)) . ': ' . $value . ' ';
                    }
                );
            }
            
            throw new \Magento\Payment\Gateway\Command\CommandException(
                !empty($errorMessage)
                    ? __($errorMessage)
                    : __('Sorry, something went wrong.')
            );
        }

        return $this->createResult(true);
    }
}
