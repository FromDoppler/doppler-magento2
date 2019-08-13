<?php
namespace Combinatoria\Doppler\Model\Lists;

use Magento\Framework\App\Request\Http;
use Combinatoria\Doppler\Helper\Doppler;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $loadedData;
    protected $rowCollection;
    protected $request;
    private $_dopplerHelper;
    protected $_idFieldName = 'listId';

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Http $request,
        Doppler  $dopplerHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->_dopplerHelper = $dopplerHelper;
    }
    public function getData()
    {
        $items = $this->_dopplerHelper->getDopplerLists();
        return [
            'totalRecords' => count($items),
            'items' => $items
        ];
    }

    public function setLimit($offset, $size)
    {
    }

    public function addOrder($field, $direction)
    {
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
    }
}