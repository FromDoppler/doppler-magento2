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
namespace Combinatoria\Doppler\Controller\Adminhtml\Lists;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Combinatoria\Doppler\Helper\Doppler;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class Delete
 * @package Combinatoria\Doppler\Controller\Adminhtml\Lists
 */

class Delete extends Action
{
    protected $filter;
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
        Filter $filter,
        JsonHelper $jsonHelper,
        Doppler  $dopplerHelper
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->_jsonHelper        = $jsonHelper;
        $this->_dopplerHelper = $dopplerHelper;

        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $deleteIds = $this->getRequest()->getParams('selected');

            $customers_list = $this->_dopplerHelper->getConfigValue('doppler_config/synch/customers_list');
            $subscribers_list = $this->_dopplerHelper->getConfigValue('doppler_config/synch/subscribers_list');

            /** @var \Magento\Sales\Model\Order $order */
            foreach ($deleteIds['selected'] as $list) {
                if($list != $customers_list && $list != $subscribers_list){
                    $this->_dopplerHelper->deleteList($list);
                }else{
                    $this->messageManager->addErrorMessage(__("You cannot delete lists currently used for synchronization"));
                }
            }

            $this->messageManager->addSuccessMessage(__('The lists has been successfully deleted'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        $this->_redirect('*/lists/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Combinatoria_Doppler::configuration');
    }
}