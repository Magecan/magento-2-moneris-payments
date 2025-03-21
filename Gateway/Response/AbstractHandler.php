<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
declare(strict_types=1);

namespace Magecan\Moneris\Gateway\Response;

abstract class AbstractHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    /**
     * @var \Magecan\Moneris\Gateway\Config\Config
     */
    protected $config;

    /**
     * @var \Magecan\Moneris\Gateway\Helper\SubjectReader
     */
    protected $subjectReader;

    /**
     * Constructor
     *
     * @param \Magecan\Moneris\Gateway\Config\Config $config
     * @param \Magecan\Moneris\Gateway\Config\Config $subjectReader
     */
    public function __construct(
        \Magecan\Moneris\Gateway\Config\Config $config,
        \Magecan\Moneris\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Processes the payment response and updates relevant data.
     *
     * @param array $handlingSubject Data related to the payment handling context.
     * @param array $response The response data from the payment gateway, containing transaction or status details.
     * @return void
     */
    abstract public function handle(array $handlingSubject, array $response);
}
