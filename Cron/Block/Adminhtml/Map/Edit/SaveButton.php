<?php
namespace Combinatoria\Doppler\Block\Adminhtml\Map\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        $url = $this->getUrl('doppler/map/save');
        return [
            'label' => __('Save Rows'),
            'class' => 'save primary',
            'on_click' => "setLocation('". $url ."')",
            'sort_order' => 90,
        ];
    }
}