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
namespace Combinatoria\Doppler\Controller\Adminhtml\Synch;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Combinatoria\Doppler\Helper\Doppler;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\AddressFactory;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;

/**
 * Class Customers
 * @package Combinatoria\Doppler\Controller\Adminhtml\Test
 */
class Customers extends Action {

    /**
     * @var JsonHelper $_jsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var PageFactory $_resultPageFactory
     */
    private $_resultPageFactory;

    /**
     * @var Doppler $_dopplerHelper
     */
    private $_dopplerHelper;


    protected $_address;
    protected $_addressFactory;

    protected $_subcriberCollectionFactory;
    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonHelper $jsonHelper
     * @param Doppler $dopplerHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonHelper $jsonHelper,
        Doppler  $dopplerHelper,
        Address $address,
        AddressFactory $addressFactory,
        CollectionFactory $subcriberCollectionFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_jsonHelper        = $jsonHelper;
        $this->_dopplerHelper = $dopplerHelper;
        $this->_address = $address;
        $this->_addressFactory = $addressFactory;
        $this->_subcriberCollectionFactory = $subcriberCollectionFactory;
        $this->cacheTypeList = $cacheTypeList;

        parent::__construct($context);
    }

    /**
     * Test connection
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if($this->_dopplerHelper->getConfigValue('doppler_config/config/enabled')) {
            $result = [];

            $lastSync = $this->_dopplerHelper->getConfigValue('doppler_config/synch/last_sync');

            if($lastSync != null){
                $customers = $this->_addressFactory->create()->getCollection()
                    ->join('sales_order',
                        'main_table.parent_id = sales_order.entity_id')
                    ->addAttributeToSelect("*")
                    ->addFieldToFilter('sales_order.created_at', ['gteq' => $lastSync.' 00:00:00'])
                    ->addFieldToFilter('main_table.address_type', ['eq' => 'shipping'])
                    ->load();

                $subscribers = $this->_subcriberCollectionFactory->create()
                    ->showCustomerInfo();
            }else{
                $customers = $this->_addressFactory->create()->getCollection()
                    ->join('sales_order',
                        'main_table.parent_id = sales_order.entity_id')
                    ->addAttributeToSelect("*")
                    ->addFieldToFilter('main_table.address_type', ['eq' => 'shipping'])
                    ->load();

                $subscribers = $this->_subcriberCollectionFactory->create()
                    ->addFieldToFilter('change_status_at', ['gteq' => $lastSync.' 00:00:00'])
                    ->showCustomerInfo();
            }

            $customers->getSelect()->group('email');

            $listId = $this->_dopplerHelper->getConfigValue('doppler_config/synch/customers_list');

            $listIdSub = $this->_dopplerHelper->getConfigValue('doppler_config/synch/subscribers_list');

            try {
                $this->_dopplerHelper->exportMultipleCustomersToDoppler($subscribers, $listIdSub);
                $this->_dopplerHelper->exportMultipleCustomersToDoppler($customers, $listId);

                $this->_dopplerHelper->setConfigValue('doppler_config/synch/last_sync', date("Y-m-d"));
                $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);

                $result['status'] = true;
                $result['content'] = __('Customers has been synchronized.');
            } catch (Exception $exception) {
                $result['status'] = false;
                $result['content'] = $exception->getMessage();
            }
        }else{
            $result['status'] = false;
            $result['content'] = __('Doppler extension is disable.');
        }

        return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));

    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Combinatoria_Doppler::configuration');
    }
}