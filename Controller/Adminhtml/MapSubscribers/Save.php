<?php
namespace Combinatoria\Doppler\Controller\Adminhtml\MapSubscribers;

use Combinatoria\Doppler\Helper\Doppler;

/**
 * Class Save
 * @package Combinatoria\Doppler\Controller\Adminhtml\MapSubscribers
 */
class Save extends \Magento\Backend\App\Action
{
    protected $map;
    protected $mapResource;

    protected $_dopplerHelper;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Combinatoria\Doppler\Model\MapSubscribersFactory $mapFactory
     * @param \Combinatoria\Doppler\Model\ResourceModel\MapSubscribersFactory $mapResource
     * @param Doppler $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Combinatoria\Doppler\Model\MapSubscribersFactory $mapFactory,
        \Combinatoria\Doppler\Model\ResourceModel\MapSubscribersFactory $mapResource,
        Doppler $helper
    ) {
        parent::__construct($context);
        $this->map = $mapFactory;
        $this->mapResource = $mapResource;
        $this->_dopplerHelper = $helper;
    }

    public function execute()
    {
        try {
            $mapData = $this->getRequest()->getParam('combinatoria_doppler_mapsubscribers_container');

            $isFieldDuplicated = $this->_dopplerHelper->isFieldDuplicated($mapData);
            if($isFieldDuplicated){
                $this->messageManager->addErrorMessage(__($isFieldDuplicated));
                $this->_redirect('*/mapsubscribers/index/scope/stores');
                return;
            }

            if($this->_dopplerHelper->getConfigValue('doppler_config/synch/subscribers_list') != $this->getRequest()->getParam('list_id')){
                $this->_dopplerHelper->setConfigValue('doppler_config/synch/last_sync', '');
            }

            $this->_dopplerHelper->setConfigValue('doppler_config/synch/subscribers_list',$this->getRequest()->getParam('list_id'));

            $mapResource = $this->mapResource->create();

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

        $this->_dopplerHelper->cleanCache();

        $this->_redirect('*/mapsubscribers/index/scope/stores');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Combinatoria_Doppler::configuration');
    }
}