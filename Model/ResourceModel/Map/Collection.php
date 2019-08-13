<?php
namespace Combinatoria\Doppler\Model\ResourceModel\Map;

use  Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(
            'Combinatoria\Doppler\Model\Map',
            'Combinatoria\Doppler\Model\ResourceModel\Map'
        );
    }
}