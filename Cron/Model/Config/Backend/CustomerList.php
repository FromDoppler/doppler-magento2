<?php
namespace Combinatoria\Doppler\Model\Config\Backend;

use Combinatoria\Doppler\Helper\Doppler;

class CustomerList extends \Magento\Framework\App\Config\Value
{
    /**
     * @var Doppler $_helper
     */
    private $_helper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        Doppler $helper
    ) {
        $data = [];
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->_helper = $helper;
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->_helper->setConfigValue('doppler_config/synch/last_sync', '');
        }

        return parent::afterSave();
    }

}