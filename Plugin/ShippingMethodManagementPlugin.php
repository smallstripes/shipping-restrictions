<?php


namespace SmallStripes\ShippingRestrictions\Plugin;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\ValidatorException;

class ShippingMethodManagementPlugin
{
    const EXCLUDE_REGION = 'smallstripes_shipping_restrictions/settings/exclude_region';
    const EXCLUDE_ZIP = 'smallstripes_shipping_restrictions/settings/exclude_zip';
    const DISABLE_PO = 'smallstripes_shipping_restrictions/settings/disable_po';

    protected $scopeConfig;

    protected $addressRepository;
    private $_rateErrorFactory;
    protected $messageManager;
    private $resultJsonFactory;
    private $shippingMethodDataFactory;

    /**
     * ShippingMethodManagementPlugin constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param AddressRepositoryInterface $addressRepository
     * @param ManagerInterface $messageManager
     * @param JsonFactory $resultJsonFactory
     * @param Error $rateErrorFactory
     * @param ShippingMethodInterfaceFactory $shippingMethodDataFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        AddressRepositoryInterface $addressRepository,
        ManagerInterface $messageManager,
        JsonFactory $resultJsonFactory,
        Error $rateErrorFactory,
        ShippingMethodInterfaceFactory $shippingMethodDataFactory
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->addressRepository = $addressRepository;
        $this->messageManager = $messageManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_rateErrorFactory = $rateErrorFactory;
        $this->shippingMethodDataFactory = $shippingMethodDataFactory;
    }

    /**
     * @param $subject
     * @param $proceed
     * @param $cartId
     * @param $addressId int
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     * @throws ValidatorException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundEstimateByAddressId($subject, $proceed, $cartId, $addressId)
    {
        $excludedRegions = explode(',', $this->getConfigValue(self::EXCLUDE_REGION));
        $address = $this->addressRepository->getById($addressId);
        if (in_array($address->getRegion()->getRegionId(), $excludedRegions)) {
            $message = new Phrase(__('Shipping to selected region is not allowed'));
            throw new ValidatorException($message);
        }
        $excludedZips = explode(',', $this->getConfigValue(self::EXCLUDE_ZIP));
        if (in_array($address->getPostcode(), $excludedZips)) {
            $message = new Phrase(__('Shipping to selected Zip/Postal is not allowed'));
            throw new ValidatorException($message);
        }
        if ($this->isPOBoxEnabled() && !empty($address->getStreet()) && $this->isPOBox($address->getStreet())) {
            $message = new Phrase('Shipping to P.O. Boxes is not allowed');
            throw new ValidatorException($message);
        }
        $result = $proceed($cartId, $addressId);
        return $result;
    }

    /**
     * @param $subject
     * @param $proceed
     * @param $cartId
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return array
     * @throws ValidatorException
     */
    public function aroundEstimateByExtendedAddress($subject, $proceed, $cartId, $address)
    {
        $excludedRegions = explode(',', $this->getConfigValue(self::EXCLUDE_REGION));
        if (!empty($address->getRegionId()) && in_array($address->getRegionId(), $excludedRegions)) {
            $message = new Phrase(__('Shipping to selected region is not allowed'));
            throw new ValidatorException($message);
        }
        $excludedZips = explode(',', $this->getConfigValue(self::EXCLUDE_ZIP));
        if (!empty($address->getPostcode()) && in_array($address->getPostcode(), $excludedZips)) {
            $message = new Phrase(__('Shipping to selected Zip/Postal is not allowed'));
            throw new ValidatorException($message);
        }
        if ($this->isPOBoxEnabled() && !empty($address->getStreet()) && $this->isPOBox($address->getStreet())) {
            $message = new Phrase(__('Shipping to P.O. Boxes is not allowed'));
            throw new ValidatorException($message);
        }
//        var_dump($this->isPOBox($address));
        $result = $proceed($cartId, $address);
        return $result;
    }

    /**
     * @param $subject
     * @param $proceed
     * @param $cartId int
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address
     * @return array
     * @throws ValidatorException
     */
    public function aroundEstimateByAddress($subject, $proceed, $cartId, $address)
    {
        $excludedRegions = explode(',', $this->getConfigValue(self::EXCLUDE_REGION));
        if (!empty($address->getRegionId()) && in_array($address->getRegionId(), $excludedRegions)) {
            $message = new Phrase(__('Shipping to selected region is not allowed'));
            throw new ValidatorException($message);
        }
        $excludedZips = explode(',', $this->getConfigValue(self::EXCLUDE_ZIP));
        if (!empty($address->getPostcode()) && in_array($address->getPostcode(), $excludedZips)) {
            $message = new Phrase(__('Shipping to selected Zip/Postal is not allowed'));
            throw new ValidatorException($message);
        }
        if ($this->isPOBoxEnabled() && !empty($address->getStreet()) && $this->isPOBox($address->getStreet())) {
            $message = new Phrase('Shipping to P.O. Boxes is not allowed');
            throw new ValidatorException($message);
        }
        $result = $proceed($cartId, $address);
        return $result;
    }

    /**
     * @param $value
     * @return mixed
     */
    private function getConfigValue($value)
    {
        return $this->scopeConfig->getValue($value, ScopeInterface::SCOPE_STORE);
    }

    /**
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
     * Are P.O. Box restrictions enabled
     *
     * @return bool
     */
    private function isPOBoxEnabled()
    {
        if ($this->getConfigValue(self::DISABLE_PO) == "1") {
            return true;
        }
        return false;
    }
}