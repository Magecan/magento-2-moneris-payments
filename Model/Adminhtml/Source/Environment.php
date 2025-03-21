<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
namespace Magecan\Moneris\Model\Adminhtml\Source;

class Environment implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => \Magecan\Moneris\Gateway\Config\Config::ENVIRONMENT_SANDBOX,
                'label' => 'Sandbox'
            ],
            [
                'value' => \Magecan\Moneris\Gateway\Config\Config::ENVIRONMENT_PRODUCTION,
                'label' => 'Production'
            ]
        ];
    }
}
