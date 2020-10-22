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
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;

/**
 * Class Subscribers
 * @package Combinatoria\Doppler\Controller\Adminhtml\Test
 */
class Subscribers extends Action {

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
        CollectionFactory $subcriberCollectionFactory
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_jsonHelper        = $jsonHelper;
        $this->_dopplerHelper = $dopplerHelper;
        $this->_subcriberCollectionFactory = $subcriberCollectionFactory;

        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = [];

        $subscribers = $this->_subcriberCollectionFactory->create()->showCustomerInfo();

        $listId = $this->_dopplerHelper->getConfigValue('doppler_config/synch/subscribers_list');

        try {
            $this->_dopplerHelper->exportMultipleCustomersToDoppler($subscribers,$listId);
            $result['status']  = true;
            $result['content'] = __('Subscribers has been synchronized.');
        } catch (Exception $exception){
            $result['status']  = false;
            $result['content'] = $exception->getMessage();
        }

        return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));

    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Combinatoria_Doppler::configuration');
    }
}