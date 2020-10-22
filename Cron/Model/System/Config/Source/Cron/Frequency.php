<?php
/**
 * Doppler Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Combinatoria
 * @package     Combinatoria_Doppler
 */
namespace Combinatoria\Doppler\Model\System\Config\Source\Cron;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Frequency
 * @package Combinatoria\Doppler\Model\System\Config\Source\Cron
 */
class Frequency implements ArrayInterface {

    public function toOptionArray()
    {
        return array(
            array('value' => '*/1 * * * *' , 'label' => __('1 minute')),
            array('value' => '*/5 * * * *' , 'label' => __('5 minutes')),
            array('value' => '*/10 * * * *', 'label' => __('10 minutes')),
            array('value' => '*/30 * * * *', 'label' => __('30 minutes')),
            array('value' => '0 */1 * * *' , 'label' => __('1 hour')),
            array('value' => '0 */2 * * *' , 'label' => __('2 hours')),
            array('value' => '0 */6 * * *' , 'label' => __('6 hours')),
            array('value' => '0 */12 * * *', 'label' => __('12 hours')),
            array('value' => '0 0 */1 * *' , 'label' => __('1 day')),
            array('value' => '0 0 */2 * *' , 'label' => __('2 days')),
            array('value' => '0 0 */3 * *' , 'label' => __('3 days')),
        );
    }
}