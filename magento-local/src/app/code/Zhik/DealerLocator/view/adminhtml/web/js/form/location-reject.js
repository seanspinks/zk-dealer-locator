/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'mage/url',
    'uiRegistry'
], function ($, Modal, urlBuilder, registry) {
    'use strict';

    return Modal.extend({
        defaults: {
            modules: {
                reasonField: '${ $.parentName }.general.rejection_reason'
            }
        },

        /**
         * Reject location action
         */
        reject: function () {
            var self = this,
                locationId = this.source.get('data.location_id'),
                reason = this.reasonField().value();

            if (!reason) {
                alert('Please provide a rejection reason.');
                return;
            }

            $.ajax({
                url: urlBuilder.build('dealerlocator/location/reject'),
                type: 'POST',
                dataType: 'json',
                data: {
                    location_id: locationId,
                    reason: reason,
                    form_key: window.FORM_KEY
                },
                showLoader: true,
                success: function (response) {
                    if (response.success) {
                        // Add success message to session and reload
                        self.closeModal();
                        window.location.reload();
                    } else {
                        alert(response.message || 'An error occurred');
                    }
                },
                error: function () {
                    alert('An error occurred while rejecting the location.');
                }
            });
        }
    });
});