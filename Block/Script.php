<?php
namespace Combinatoria\Doppler\Block;

use Combinatoria\Doppler\Helper\Doppler;

class Script extends \Magento\Framework\View\Element\Template
{
    private $_dopplerHelper;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,Doppler  $dopplerHelper)
    {
        parent::__construct($context);
        $this->_dopplerHelper = $dopplerHelper;
    }

    public function getPopUpScript(){
        return $this->_dopplerHelper->getConfigValue('doppler_config/scripts/popup');
    }

    public function getTrackScript(){
        return $this->_dopplerHelper->getConfigValue('doppler_config/scripts/tracking');
    }
}