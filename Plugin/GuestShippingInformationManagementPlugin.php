<?php

namespace SmallStripes\ShippingRestrictions\Plugin;

use SmallStripes\ShippingRestrictions\Model\Validator;
use Magento\Framework\Phrase;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class GuestShippingInformationManagementPlugin
 * @package SmallStripes\ShippingRestrictions\Plugin
 */
class GuestShippingInformationManagementPlugin
{
    /**
     * @var \SmallStripes\ShippingRestrictions\Model\Validator
     */
    private $ruleValidator;

    /**
     * GuestShippingInformationManagementPlugin constructor.
     * @param \SmallStripes\ShippingRestrictions\Model\Validator $ruleValidator
     */
    public function __construct(
        Validator $ruleValidator
    )
    {
        $this->ruleValidator = $ruleValidator;
    }


    /**
     * @param $subject
     * @param $cartId int
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return null
     * @throws ValidatorException
     */
    public function beforeSaveAddressInformation($subject, $cartId, $addressInformation)
    {
        $regionId = $addressInformation->getShippingAddress()->getRegionId();
        if ($this->ruleValidator->isRegionRestricted($regionId)) {
            $message = new Phrase('Shipping to selected region is not allowed');
            throw new ValidatorException($message);
        }
        $postcode = $addressInformation->getShippingAddress()->getPostcode();
        if ($this->ruleValidator->isPostcodeRestricted($postcode)) {
            $message = new Phrase('Shipping to selected Zip/Postcode is not allowed');
            throw new ValidatorException($message);
        }

        $street = $addressInformation->getShippingAddress()->getStreet();
        if ($this->ruleValidator->isPOBoxRestricted($street)) {
            $message = new Phrase('Shipping to P.O. Boxes is not allowed');
            throw new ValidatorException($message);
        }
        return null;
    }
}
