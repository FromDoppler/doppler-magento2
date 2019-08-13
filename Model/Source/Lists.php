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
            'label' => 'Select a list',
            'value' => '',
        ];
        foreach($dopplerLists as $list)
        {
            $options[] = [
                'label' => $list['name'],
                'value' => $list['listId']
            ];
        }
        return $options;
    }
}