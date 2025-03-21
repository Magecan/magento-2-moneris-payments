<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Gateway\Config;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    public const KEY_ACTIVE = 'active';
    public const KEY_ENVIRONMENT = 'environment';
    public const KEY_STORE_ID = 'store_id';
    public const KEY_API_TOKEN = 'api_token';
    public const KEY_CHECKOUT_ID = 'checkout_id';
    public const KEY_SANDBOX_CHECKOUT_URL = 'sandbox_checkout_url';
    public const KEY_PRODUCTION_CHECKOUT_URL = 'production_checkout_url';
    public const KEY_CCTYPES_MONERIS_MAPPER = 'cctypes_moneris_mapper';
    public const KEY_IFRAME_HEIGHT = 'iframe_height';
    public const KEY_VAULT_ACTIVE = 'vault_active';
    public const KEY_ASK_CVV = 'ask_cvv';
    public const KEY_DYNAMIC_DESCRIPTOR = 'dynamic_descriptor';

    public const ENVIRONMENT_PRODUCTION = 'prod';
    public const ENVIRONMENT_SANDBOX = 'qa';

    /**
     * Get environment
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->getValue(self::KEY_ENVIRONMENT);
    }

    /**
     * Get Iframe Height
     *
     * @return string
     */
    public function getIframeHeight(): string
    {
        return $this->getValue(self::KEY_IFRAME_HEIGHT);
    }

    /**
     * Get Moneris store ID (not the Magento store ID).
     *
     * @return string|null
     */
    public function getStoreId()
    {
        return $this->getValue(self::KEY_STORE_ID);
    }

    /**
     * Get API token for Moneris payment gateway.
     *
     * @return string|null
     */
    public function getApiToken()
    {
        return $this->getValue(self::KEY_API_TOKEN);
    }

    /**
     * Get Payment configuration status
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * Get Checkout ID
     *
     * @param int $storeId
     * @return mixed|null
     */
    public function getCheckoutId()
    {
        return $this->getValue(self::KEY_CHECKOUT_ID);
    }

    /**
     * Get checkout URL based on the environment (sandbox or production).
     *
     * @return string|null
     */
    public function getCheckoutUrl()
    {
        if ($this->getEnvironment() === self::ENVIRONMENT_PRODUCTION) {
            return $this->getValue(self::KEY_PRODUCTION_CHECKOUT_URL);
        } else {
            return $this->getValue(self::KEY_SANDBOX_CHECKOUT_URL);
        }
    }

    /**
     * Get credit card types mapping for Moneris payment gateway.
     *
     * @return string|null
     */
    public function getCctypesMonerisMapper()
    {
        return $this->getValue(self::KEY_CCTYPES_MONERIS_MAPPER);
    }

    /**
     * Check if vault storage for Moneris is active.
     *
     * @return bool
     */
    public function isVaultActive(): bool
    {
        return (bool) $this->getValue(self::KEY_VAULT_ACTIVE);
    }

    /**
     * Check if CVV is required for Moneris transactions.
     *
     * @return bool
     */
    public function isAskCvv(): bool
    {
        return (bool) $this->getValue(self::KEY_ASK_CVV);
    }

    /**
     * Get dynamic descriptor for Moneris transactions.
     *
     * @return string|null
     */
    public function getDynamicDescriptor(): string|null
    {
        return $this->getValue(self::KEY_DYNAMIC_DESCRIPTOR);
    }
}
