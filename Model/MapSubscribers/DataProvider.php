<?php
namespace Combinatoria\Doppler\Model\MapSubscribers;

use Combinatoria\Doppler\Helper\Doppler;

/**
 * Class DataProvider
 * @package Combinatoria\Doppler\Model\MapSubscribers
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $loadedData;
    protected $rowCollection;

    /**
     * @var Doppler $_helper
     */
    private $_helper;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Combinatoria\Doppler\Model\ResourceModel\MapSubscribers\Collection $collection
     * @param \Combinatoria\Doppler\Model\ResourceModel\MapSubscribers\CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Combinatoria\Doppler\Model\ResourceModel\MapSubscribers\Collection $collection,
        \Combinatoria\Doppler\Model\ResourceModel\MapSubscribers\CollectionFactory $collectionFactory,
        Doppler $helper,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collection;
        $this->rowCollection = $collectionFactory;
        $this->_helper = $helper;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $collection = $this->rowCollection->create()->setOrder('id', 'ASC');
        $items = $collection->getItems();
        foreach ($items as $item) {
            $this->loadedData['stores']['combinatoria_doppler_mapsubscribers_container'][] = $item->getData();
        }

        $this->loadedData['stores']['list_id'] = $this->_helper->getConfigValue('doppler_config/synch/subscribers_list');

        return $this->loadedData;
    }
}