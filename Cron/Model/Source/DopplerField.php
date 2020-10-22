<?php
namespace Combinatoria\Doppler\Model\Source;

use Combinatoria\Doppler\Helper\Doppler;

class DopplerField implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Doppler $_dopplerHelper
     */
    private $_dopplerHelper;

    protected $_attributeFactory;

    public function __construct(
        Doppler  $dopplerHelper
    )
    {
        $this->_dopplerHelper = $dopplerHelper;
    }

    public function toOptionArray()
    {
        $dopplerFields = $this->_dopplerHelper->getDopplerFields();
        $options[] = [
            'label' => 'Select a field',
            'value' => '',
        ];
        foreach($dopplerFields as $field)
        {
            $options[] = [
                'label' => $field,
                'value' => $field,
            ];
        }

        usort($options, array('Combinatoria\Doppler\Model\Source\DopplerField','compareByName'));

        return $options;
    }

    private static function compareByName($a, $b) {
        return strcmp(strtolower($a["label"]), strtolower($b["label"]));
    }
}