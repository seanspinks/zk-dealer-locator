<?php
namespace Zhik\DealerLocator\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class GeolocationService implements OptionSourceInterface
{
    /**
     * Get options for geolocation service
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'ipapi', 'label' => __('IP-API (Free)')],
            ['value' => 'ipinfo', 'label' => __('IPInfo (Requires Token)')],
        ];
    }
}