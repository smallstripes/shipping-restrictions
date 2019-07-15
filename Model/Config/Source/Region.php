<?php


namespace SmallStripes\ShippingRestrictions\Model\Config\Source;

use Magento\Directory\Model\Config\Source\Country;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Option\ArrayInterface;

class Region implements ArrayInterface
{

    /**
     * @var Country
     */
    protected $_country;

    /**
     * @var RegionFactory
     */
    protected $_regionFactory;

    public function __construct(
        Country $_country,
        RegionFactory $_regionFactory
    )
    {
        $this->_country = $_country;
        $this->_regionFactory = $_regionFactory;
    }

    /**
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        /** @var \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection */
        $regionCollection = $this->_regionFactory->create()->getCollection();
        $regionCollection->addCountryFilter('US');
        $regions = [];
        $propertyMap = [
            'value' => 'region_id',
            'title' => 'default_name',
            'country_id' => 'country_id',
        ];
        foreach ($regionCollection as $item) {
            $option = [];
            foreach ($propertyMap as $code => $field) {
                $option[$code] = $item->getData($field);
            }
            $option['label'] = $item->getName();
            $regions[] = $option;
        }
        return $regions;
    }
}