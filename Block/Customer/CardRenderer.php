<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Block\Customer;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;
use Magecan\Moneris\Model\ConfigProvider;

class CardRenderer extends AbstractCardRenderer
{
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token): bool
    {
        return $token->getPaymentMethodCode() === ConfigProvider::CODE;
    }

    /**
     * Get Number Last 4 Digits
     *
     * @return string
     */
    public function getNumberLast4Digits(): string
    {
        return $this->getTokenDetails()['maskedCC'];
    }

    /**
     * Get exp Date
     *
     * @return string
     */
    public function getExpDate(): string
    {
        return $this->getTokenDetails()['expirationDate'];
    }

    /**
     * Get Icon Url
     *
     * @return string
     */
    public function getIconUrl(): string
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['url'];
    }

    /**
     * Get Icon Height
     *
     * @return int
     */
    public function getIconHeight(): int
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['height'];
    }

    /**
     * Get Icon Width
     *
     * @return int
     */
    public function getIconWidth(): int
    {
        return $this->getIconForType($this->getTokenDetails()['type'])['width'];
    }
}
