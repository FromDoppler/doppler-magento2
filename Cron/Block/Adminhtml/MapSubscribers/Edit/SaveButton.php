<?php
namespace Combinatoria\Doppler\Block\Adminhtml\MapSubscribers\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton;

/**
 * Class SaveButton
 * @package Combinatoria\Doppler\Block\Adminhtml\MapSubscribers\Edit
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        $url = $this->getUrl('doppler/mapsubscribers/save');
        return [
            'label' => __('Save Rows'),
            'class' => 'save primary',
            'on_click' => "setLocation('". $url ."')",
            'sort_order' => 90,
        ];
    }
}