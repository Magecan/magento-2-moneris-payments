<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Gateway\Response;

/**
 * Payment Details Handler
 */
class PreloadHandler extends AbstractHandler
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Constructor
     *
     * @param \Magecan\Moneris\Gateway\Config\Config $config
     * @param \Magecan\Moneris\Gateway\Config\Config $subjectReader
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magecan\Moneris\Gateway\Config\Config $config,
        \Magecan\Moneris\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($config, $subjectReader);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Handles response data and sets the ticket in the checkout session.
     *
     * @param array $handlingSubject Context data provided by the payment gateway.
     * @param array $response Response data from the payment gateway, containing the ticket.
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $this->checkoutSession->setTicket($response['ticket']);
    }
}
