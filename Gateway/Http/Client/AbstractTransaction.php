<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */

namespace Magecan\Moneris\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class AbstractTransaction
 */
abstract class AbstractTransaction implements ClientInterface
{
    /**
     * @var LoggerInterface
     */
    protected $psrLoger;

    /**
     * @var \Magecan\Moneris\Gateway\Config\Config
     */
    protected $config;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    protected $paymentLogger;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $psrLoger
     * @param \Magecan\Moneris\Gateway\Config\Config $config
     * @param \Magento\Payment\Model\Method\Logger $paymentLogger
     * @param \Magecan\Moneris\Gateway\Helper\SubjectReader $subjectReader
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        \Psr\Log\LoggerInterface $psrLoger,
        \Magecan\Moneris\Gateway\Config\Config $config,
        \Magento\Payment\Model\Method\Logger $paymentLogger,
        \Magecan\Moneris\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->psrLoger = $psrLoger;
        $this->config = $config;
        $this->paymentLogger = $paymentLogger;
        $this->subjectReader = $subjectReader;
        $this->curl = $curl;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();

        try {
            $response = $this->process($data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            $this->psrLoger->critical($message);
            throw new ClientException($message);
        }

        return $response;
    }

    /**
     * Process http request
     *
     * @param array $data
     * @return \Moneris\Result\Error|\Moneris\Result\Successful
     */
    abstract protected function process(array $data);

    /**
     * Retrieves the CURL response from Moneris.
     *
     * @param array $requestData The request data to be sent to Moneris.
     * @return array The response body from Moneris, decoded from JSON.
     */
    protected function getCurlResponse($requestData)
    {
        $url = $this->config->getCheckoutUrl();
        
        $this->curl->post($url, $this->serializer->serialize($requestData));
        $body = $this->serializer->unserialize($this->curl->getBody());

        $this->paymentLogger->debug(['request' => $requestData, 'response' => $body]);
        
        return $body['response'];
    }
}
