<?php
namespace Combinatoria\Doppler\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class MapSubscribers
 * @package Combinatoria\Doppler\Model
 */
class MapSubscribers extends AbstractModel
{
    const CACHE_TAG = 'combinatoria_doppler';
    protected $_cacheTag = 'combinatoria_doppler';
    protected $_eventPrefix = 'combinatoria_doppler';
    protected function _construct()
    {
        $this->_init('Combinatoria\Doppler\Model\ResourceModel\MapSubscribers');
    }
}