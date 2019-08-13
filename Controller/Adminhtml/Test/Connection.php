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
namespace Combinatoria\Doppler\Controller\Adminhtml\Test;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Combinatoria\Doppler\Helper\Doppler;

/**
 * Class Connection
 * @package Combinatoria\Doppler\Controller\Adminhtml\Test
 */
class Connection extends Action {

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
        Doppler  $dopplerHelper
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_jsonHelper        = $jsonHelper;
        $this->_dopplerHelper = $dopplerHelper;

        parent::__construct($context);
    }

    /**
     * Test connection
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = [];
        try {

            $this->_dopplerHelper->testAPIConnection();
            $result['status']  = true;
            $result['content'] = __('Connection sucefully.');

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