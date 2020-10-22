<?php
namespace Combinatoria\Doppler\Model\MapCustomers;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $loadedData;
    protected $rowCollection;
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Combinatoria\Doppler\Model\ResourceModel\MapCustomers\Collection $collection,
        \Combinatoria\Doppler\Model\ResourceModel\MapCustomers\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collection;
        $this->rowCollection = $collectionFactory;
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
            $this->loadedData['stores']['combinatoria_doppler_mapcustomers_container'][] = $item->getData();
        }
        return $this->loadedData;
    }
}