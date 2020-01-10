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
use Magento\Framework\App\RequestInterface;
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

    protected $cacheTypeList;
    /**
     * Constructor
     *
     * @param Doppler $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Doppler $helper,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->cacheTypeList = $cacheTypeList;
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

                    /* Activate default lists */
                    if($this->_helper->getConfigValue('doppler_config/synch/customers_list') == ''){
                        $lists = $this->_helper->getDopplerLists();
                        foreach ($lists as $list){
                            if($list['name'] == 'Clientes de Magento' || $list['name'] == 'Magento Clients'){
                                $this->_helper->setConfigValue('doppler_config/synch/customers_list', $list['listId']);
                                break;
                            }
                        }

                        try {
                            $name = 'Compradores Magento';
                            $listId = $this->_helper->createDopplerLists($name);
                            $this->_helper->setConfigValue('doppler_config/synch/customers_list', $listId);
                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage(__($e->getMessage()));
                        }
                    }

                    if($this->_helper->getConfigValue('doppler_config/synch/subscribers_list') == ''){
                        if(!isset($lists)){
                            $lists = $this->_helper->getDopplerLists();
                        }
                        foreach ($lists as $list){
                            if($list['name'] == 'Suscriptores de Magento' || $list['name'] == 'Magento Subscribers'){
                                $this->_helper->setConfigValue('doppler_config/synch/subscribers_list', $list['listId']);
                                break;
                            }
                        }

                        try {
                            $name = 'Suscriptores Magento';
                            $listId = $this->_helper->createDopplerLists($name);
                            $this->_helper->setConfigValue('doppler_config/synch/subscribers_list', $listId);
                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage(__($e->getMessage()));
                        }
                    }

                    $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
                    $this->cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);

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
                    $this->_helper->setConfigValue('doppler_config/config/enabled', 1);
                    $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
                    $this->cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
                }
            }
        }
    }
}