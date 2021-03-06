<?php
namespace Combinatoria\Doppler\Model\ResourceModel\MapCustomers;

use  Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(
            'Combinatoria\Doppler\Model\MapCustomers',
            'Combinatoria\Doppler\Model\ResourceModel\MapCustomers'
        );
    }
}