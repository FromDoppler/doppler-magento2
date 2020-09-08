<?php
namespace Combinatoria\Doppler\Model\Map;
use Combinatoria\Doppler\Helper\Doppler;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $loadedData;
    protected $rowCollection;

    /**
     * @var Doppler $_helper
     */
    private $_helper;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Combinatoria\Doppler\Model\ResourceModel\Map\Collection $collection,
        \Combinatoria\Doppler\Model\ResourceModel\Map\CollectionFactory $collectionFactory,
        Doppler $helper,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collection;
        $this->rowCollection = $collectionFactory;
        $this->_helper = $helper;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $collection = $this->rowCollection->create()->setOrder('id', 'ASC');
        $items = $collection->getItems();
        foreach ($items as $item) {
            $this->loadedData['stores']['combinatoria_doppler_map_container'][] = $item->getData();
        }
        $this->loadedData['stores']['list_id'] = $this->_helper->getConfigValue('doppler_config/synch/customers_list');
        return $this->loadedData;
    }
}