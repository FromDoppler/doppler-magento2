<?php
namespace Combinatoria\Doppler\Model\Source;

use Combinatoria\Doppler\Helper\Doppler;

class Lists implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Doppler $_dopplerHelper
     */
    private $_dopplerHelper;

    protected $request;

    public function __construct(
        Doppler  $dopplerHelper,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->_dopplerHelper = $dopplerHelper;
        $this->request = $request;
    }

    public function toOptionArray()
    {
        $dopplerLists = $this->_dopplerHelper->getDopplerLists();

        $controller = $this->request->getControllerName();

        $options[] = [
            'label' => __('Select a list'),
            'value' => '',
        ];
        foreach($dopplerLists as $list)
        {
            if($controller == 'map'){
                if($list['listId'] == $this->_dopplerHelper->getConfigValue('doppler_config/synch/subscribers_list')|| $list['listId'] == $this->_dopplerHelper->getConfigValue('doppler_config/synch/clients_list')){
                    continue;
                }
            }

            if($controller == 'mapsubscribers'){
                if($list['listId'] == $this->_dopplerHelper->getConfigValue('doppler_config/synch/customers_list')|| $list['listId'] == $this->_dopplerHelper->getConfigValue('doppler_config/synch/clients_list')){
                    continue;
                }
            }

            if($controller == 'mapcustomers'){
                if($list['listId'] == $this->_dopplerHelper->getConfigValue('doppler_config/synch/customers_list')|| $list['listId'] == $this->_dopplerHelper->getConfigValue('doppler_config/synch/subscribers_list')){
                    continue;
                }
            }

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
