<?php
namespace Combinatoria\Doppler\Model\Source;

use Combinatoria\Doppler\Helper\Doppler;

class Lists implements \Magento\Framework\Option\ArrayInterface
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
        $dopplerLists = $this->_dopplerHelper->getDopplerLists();
        $options[] = [
            'label' => __('Select a list'),
            'value' => '',
        ];
        foreach($dopplerLists as $list)
        {
            $options[] = [
                'label' => $list['name'],
                'value' => $list['listId']
            ];
        }

        usort($options, array('Combinatoria\Doppler\Model\Source\Lists','compareByName'));

        return $options;
    }

    private static function compareByName($a, $b) {
        return strcmp(strtolower($a["label"]), strtolower($b["label"]));
    }
}