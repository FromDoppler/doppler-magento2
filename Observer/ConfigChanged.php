<?php
/**
 * Doppler extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @package    Combinatoria\Doppler\Helper
 * @author     Combinatoria
 */
namespace Combinatoria\Doppler\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Combinatoria\Doppler\Helper\Doppler;
use Magento\Framework\Message\ManagerInterface;
/**
 * Class ConfigChanged
 * @package Combinatoria/Doppler/Observer
 */
class ConfigChanged implements ObserverInterface{

    /**
     * @var Doppler $_helper
     */
    private $_helper;

    /**
     * @var StoreManagerInterface $_storeManager
     */
    private $_storeManager;

    private $messageManager;
    /**
     * Constructor
     *
     * @param Doppler $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Doppler $helper,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager
    ) {
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }


    /**
     * Execute the observer
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $syncDopplerCronFrequency = $this->_helper->getConfigValue(Doppler::CONFIG_DOPPLER_SYNC_CRON_FREQUENCY_PATH);

        if ($syncDopplerCronFrequency != ''){
            $this->_helper->setConfigValue(Doppler::CONFIG_DOPPLER_SYNC_CRON_EXPR_PATH, $syncDopplerCronFrequency);
            $this->_helper->setConfigValue(Doppler::CONFIG_DOPPLER_SYNC_CRON_MODEL_PATH, null);
        }

        if($this->_helper->getConfigValue('doppler_config/config/enabled')) {
            if(!$this->_helper->getConfigValue('doppler_config/integration/enabled')){
                try{
                    $message = $this->_helper->putIntegration();
                    $this->_helper->setConfigValue('doppler_config/integration/enabled', 1);
                }catch (\Exception $e){
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                }
            }
        }else{
            if($this->_helper->getConfigValue('doppler_config/integration/enabled')){
                try{
                    $message = $this->_helper->deleteIntegration();
                    $this->_helper->setConfigValue('doppler_config/integration/enabled', 0);
                }catch(\Exception $e){
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                }
            }
        }
    }
}