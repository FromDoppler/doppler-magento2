<?php
namespace Combinatoria\Doppler\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
class Map extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('combinatoria_doppler_leadmap', 'id');
    }
    public function deleteFormat()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['id > ?' => 0]
        );
    }
}