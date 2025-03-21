<?php
/**
 * Copyright © Magecan, Inc. All rights reserved.
 */
declare(strict_types=1);

namespace Magecan\Moneris\Block\Adminhtml\Order\View\Info;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

/**
 * Payment information block for Moneris payment method.
 */
class PaymentDetails extends ConfigurableInfo
{
    /**
     * Returns localized label for payment info block.
     *
     * @param string $field
     * @return string | Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }
}
