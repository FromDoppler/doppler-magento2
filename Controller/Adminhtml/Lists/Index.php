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

/**
 * Class Index
 * @package Combinatoria\Doppler\Controller\Adminhtml\Lists
 */

class Index extends Action
{
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

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Combinatoria_Doppler::configuration');
    }
}