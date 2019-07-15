<?php

namespace SmallStripes\ShippingRestrictions\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ZipCodes
 * @package SmallStripes\ShippingRestrictions\Model\Config\Backend
 */
class ZipCodes extends Value
{

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        if ($this->isValueChanged()) {
            $value = $this->getValue();
            if (empty($value)) {
                return $this;
            }
            $valuesArray = explode(',', $value);
            if (in_array("", $valuesArray)) {
                throw new LocalizedException(__('Please correct the list of zip codes'));
            }
        }
        return $this;
    }

}