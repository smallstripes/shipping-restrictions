<?php

namespace SmallStripes\ShippingRestrictions\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Validator
{

    const EXCLUDE_REGION = 'smallstripes_shipping_restrictions/settings/exclude_region';
    const EXCLUDE_ZIP = 'smallstripes_shipping_restrictions/settings/exclude_zip';
    const DISABLE_PO = 'smallstripes_shipping_restrictions/settings/disable_po';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Validator constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if requested region is restricted
     * @param $regionId
     * @return bool
     */
    public function isRegionRestricted($regionId)
    {
        $excludedRegions = explode(',', $this->getConfigValue(self::EXCLUDE_REGION));
        if (in_array($regionId, $excludedRegions)) {
            return true;
        }
        return false;
    }

    /**
     * Check if requested postcode is restricted
     * @param $postcode
     * @return bool
     */
    public function isPostcodeRestricted($postcode)
    {
        $excludedZips = explode(',', $this->getConfigValue(self::EXCLUDE_ZIP));
        if (!empty($postcode) && in_array($postcode, $excludedZips)) {
            return true;
        }
        return false;
    }

    /**
     * Check if PO Box restriction in enabled
     * @param $street
     * @return bool
     */
    public function isPOBoxRestricted($street)
    {
        if ($this->getConfigValue(self::DISABLE_PO) == "1" && !empty($street) && $this->isPOBox($street)) {
            return true;
        }
        return false;
    }

    /**
     * Check if address string contains po box substring
     * @param $street
     * @return bool
     */
    private function isPOBox($street)
    {
        if (isset($street[0]) && preg_match("/p\.* *o\.* *box/i", $street[0])) {
            return true;
        }
        if (isset($street[1]) && preg_match("/p\.* *o\.* *box/i", $street[1])) {
            return true;
        }
        return false;
    }

    /**
     * Get configuration value for current store
     * @param $value
     * @return mixed
     */
    private function getConfigValue($value)
    {
        return $this->scopeConfig->getValue($value, ScopeInterface::SCOPE_STORE);
    }

}