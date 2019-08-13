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

define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    "mage/translate",
    "jquery/ui"
], function ($, alert, $t) {
    "use strict";

    $.widget('doppler.synchSubscribers', {
        options: {
            ajaxUrl: '',
            synchSubscribersButton: '#doppler_config_synch_synch_subscribers'
        },
        _create: function () {
            var self = this;

            $(this.options.synchSubscribersButton).click(function (e) {
                e.preventDefault();
                self._ajaxSubmit();
            });
        },

        _ajaxSubmit: function () {
            $.ajax({
                url: this.options.ajaxUrl,
                dataType: 'json',
                showLoader: true,
                data: {'all':true},
                success: function (result) {
                    alert({
                        title: result.status ? $t('Success') : $t('Error'),
                        content: result.content
                    });
                }
            });
        }
    });

    return $.doppler.synchSubscribers;
});