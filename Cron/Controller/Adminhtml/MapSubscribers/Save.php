<?php
namespace Combinatoria\Doppler\Controller\Adminhtml\MapSubscribers;

/**
 * Class Save
 * @package Combinatoria\Doppler\Controller\Adminhtml\MapSubscribers
 */
class Save extends \Magento\Backend\App\Action
{
    protected $map;
    protected $mapResource;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Combinatoria\Doppler\Model\MapSubscribersFactory $mapFactory
     * @param \Combinatoria\Doppler\Model\ResourceModel\MapSubscribersFactory $mapResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Combinatoria\Doppler\Model\MapSubscribersFactory $mapFactory,
        \Combinatoria\Doppler\Model\ResourceModel\MapSubscribersFactory $mapResource
    ) {
        parent::__construct($context);
        $this->map = $mapFactory;
        $this->mapResource = $mapResource;
    }
    public function execute()
    {
        try {
            $mapResource = $this->mapResource->create();
            $mapData = $this->getRequest()->getParam('combinatoria_doppler_mapsubscribers_container');
            $mapResource->deleteFormat();
            if (is_array($mapData) && !empty($mapData)) {
                foreach ($mapData as $mapDatum) {
                    $model = $this->map->create();
                    unset($mapDatum['id']);
                    $model->addData($mapDatum);
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Rows have been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        $this->_redirect('*/mapsubscribers/index/scope/stores');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Combinatoria_Doppler::configuration');
    }
}