<?php
namespace Combinatoria\Doppler\Model\Config\Backend;

use Combinatoria\Doppler\Helper\Doppler;

class Username extends \Magento\Framework\App\Config\Value
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
            $old = $this->getOldValue();
            $new = $this->getValue();

            if($old != $new){
                $this->_helper->setConfigValue('doppler_config/config/key_changed', 1);
                $this->_helper->setConfigValue('doppler_config/config/username_old', $old);
            }
        }
        return parent::afterSave();
    }

}