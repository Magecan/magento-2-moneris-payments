<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Controller\Payment;

/**
 * Implementing Preload Server-to-Server Logic.
 */
class Preload extends \Magento\Framework\App\Action\Action
{
    private const HTTP_BAD_REQUEST = 400;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->commandPool = $commandPool;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        try {
            $this->commandPool->get('preload')->execute([
                'billingAddress' => $this->getRequest()->getParam('billingAddress')
            ]);

            $resultJson->setData(['ticket' => $this->checkoutSession->getTicket()]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData(['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData(['message' => __('Sorry, something went wrong.')]);
        }

        return $resultJson;
    }
}
