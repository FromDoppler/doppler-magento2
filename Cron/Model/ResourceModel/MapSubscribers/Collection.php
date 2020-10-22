<?php
namespace Combinatoria\Doppler\Model\ResourceModel\MapSubscribers;

use  Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected function _construct()
    {
        $this->_init(
            'Combinatoria\Doppler\Model\MapSubscribers',
            'Combinatoria\Doppler\Model\ResourceModel\MapSubscribers'
        );
    }
}