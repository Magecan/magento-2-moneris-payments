<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */

declare(strict_types=1);

namespace Magecan\Moneris\Model;

/**
 * Retrieves config needed for checkout
 *
 */
class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    public const CODE = 'moneris';

    public const CC_VAULT_CODE = 'moneris_vault';

    /**
     * @var \Magecan\Moneris\Gateway\Config\Config
     */
    protected $config;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magecan\Moneris\Gateway\Config\Config $config
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magecan\Moneris\Gateway\Config\Config $config,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $monerisConfig['active'] = $this->config->isActive();
        if ($monerisConfig['active']) {
            $monerisConfig['preloadUrl'] = $this->urlBuilder->getUrl('moneris/request/preload');
            $monerisConfig['receiptUrl'] = $this->urlBuilder->getUrl('moneris/request/receipt');
            $monerisConfig['environment'] = $this->config->getEnvironment();
            $monerisConfig['iframeHeight'] = $this->config->getIframeHeight();
            $monerisConfig['ccVaultCode'] = self::CC_VAULT_CODE;
        }

        if ($this->config->isVaultActive()) {
            $monerisVaultConfig = ['is_enabled' => true];
        } else {
            $monerisVaultConfig = [];
        }

        return [
            'payment' => [
                'moneris' => $monerisConfig
            ],
            'vault' => [
                'moneris_vault' => $monerisVaultConfig
            ]
        ];
    }
}
