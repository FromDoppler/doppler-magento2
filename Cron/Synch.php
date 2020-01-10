<?php
/**
 * Doppler extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @package    Combinatoria_Doppler
 * @author     Combinatoria
 */
namespace Combinatoria\Doppler\Cron;

use Exception;
use Combinatoria\Doppler\Helper\Doppler;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\AddressFactory;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;

/**
 * Class OrdersSync
 * @package Combinatoria\Doppler\Cron\Sync
 */
class Synch {

    /**
     * @var Doppler $_dopplerHelper
     */
    private $_dopplerHelper;

    protected $_address;
    protected $_addressFactory;

    protected $_subcriberCollectionFactory;

    /**
     * class constructor.
     *
     * @param Doppler $dopplerHelper
     */
    public function __construct(
        Doppler  $dopplerHelper,
        Address $address,
        AddressFactory $addressFactory,
        CollectionFactory $subcriberCollectionFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    )
    {
        $this->_dopplerHelper = $dopplerHelper;
        $this->_address = $address;
        $this->_addressFactory = $addressFactory;
        $this->_subcriberCollectionFactory = $subcriberCollectionFactory;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Sync all customers
     *
     * @return void
     */
    public function execute(){
        if($this->_dopplerHelper->getConfigValue('doppler_config/synch/enabled_cron')){
            $result = [];

            $lastSync = $this->_dopplerHelper->getConfigValue('doppler_config/synch/last_sync');

            if($lastSync != null){
                $customers = $this->_addressFactory->create()->getCollection()
                    ->join('sales_order',
                        'main_table.parent_id = sales_order.entity_id')
                    ->addAttributeToSelect("*")
                    ->addFieldToFilter('sales_order.created_at', ['gteq' => $lastSync.' 00:00:00'])
                    ->addFieldToFilter('main_table.address_type', ['eq' => 'billing'])
                    ->setOrder(
                        'sales_order.created_at',
                        'desc'
                    )
                    ->load();

                $subscribers = $this->_subcriberCollectionFactory->create()
                    ->showCustomerInfo();
            }else{
                $customers = $this->_addressFactory->create()->getCollection()
                    ->join('sales_order',
                        'main_table.parent_id = sales_order.entity_id')
                    ->addAttributeToSelect("*")
                    ->addFieldToFilter('main_table.address_type', ['eq' => 'billing'])
                    ->setOrder(
                        'sales_order.created_at',
                        'desc'
                    )
                    ->load();

                $subscribers = $this->_subcriberCollectionFactory->create()
                    ->addFieldToFilter('change_status_at', ['gteq' => $lastSync.' 00:00:00'])
                    ->showCustomerInfo();
            }

//            $customers->getSelect()->group('email');

            $listId = $this->_dopplerHelper->getConfigValue('doppler_config/synch/customers_list');

            $listIdSub = $this->_dopplerHelper->getConfigValue('doppler_config/synch/subscribers_list');

            try {
                $this->_dopplerHelper->exportMultipleCustomersToDoppler($subscribers, $listIdSub);
                $this->_dopplerHelper->exportMultipleCustomersToDoppler($customers, $listId);

                $this->_dopplerHelper->setConfigValue('doppler_config/synch/last_sync', date("Y-m-d"));
                $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);

                $result['status']  = true;
                $result['content'] = __('Customers has been synchronized.');
            } catch (Exception $exception){
                $this->_dopplerHelper->log($exception->getMessage(),"doppler_cron_error.log");
            }
        }

        return;
    }
}