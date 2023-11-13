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
namespace Combinatoria\Doppler\Controller\Adminhtml\Map;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Combinatoria\Doppler\Helper\Doppler;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Connection
 * @package Combinatoria\Doppler\Controller\Adminhtml\Map
 */

class Index extends Action
{
    private $resultPageFactory;
    private $jsonHelper;
    private $dopplerHelper;

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
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dopplerHelper = $dopplerHelper;
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
