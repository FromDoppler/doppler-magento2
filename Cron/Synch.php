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
        CollectionFactory $subcriberCollectionFactory
    )
    {
        $this->_dopplerHelper = $dopplerHelper;
        $this->_address = $address;
        $this->_addressFactory = $addressFactory;
        $this->_subcriberCollectionFactory = $subcriberCollectionFactory;
    }

    /**
     * Sync all customers
     *
     * @return void
     */
    public function execute(){
        if($this->_dopplerHelper->getConfigValue('doppler_config/synch/enabled_cron')){
            $result = [];

            $customers = $this->_addressFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->load();
            $customers->getSelect()->group('email');

            $listId = $this->_dopplerHelper->getConfigValue('doppler_config/synch/customers_list');

            $subscribers = $this->_subcriberCollectionFactory->create()->showCustomerInfo();

            $listIdSub = $this->_dopplerHelper->getConfigValue('doppler_config/synch/subscribers_list');

            try {
                $this->_dopplerHelper->exportMultipleCustomersToDoppler($customers,$listId);
                $this->_dopplerHelper->exportMultipleCustomersToDoppler($subscribers,$listIdSub);

                $result['status']  = true;
                $result['content'] = __('Customers has been synchronized.');
            } catch (Exception $exception){
                $this->_dopplerHelper->log($exception->getMessage(),"doppler_cron_error.log");
            }
        }

        return;
    }
}