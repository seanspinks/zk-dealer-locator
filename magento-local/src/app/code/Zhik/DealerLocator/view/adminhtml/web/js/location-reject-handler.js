/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/url',
    'mage/translate'
], function ($, modal, urlBuilder, $t) {
    'use strict';

    return function (config, element) {
        
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: $t('Reject Location'),
            buttons: [{
                text: $t('Cancel'),
                class: 'action-secondary',
                click: function () {
                    this.closeModal();
                }
            }, {
                text: $t('Reject'),
                class: 'action-primary',
                click: function () {
                    var self = this;
                    var reason = $('#rejection-reason').val();
                    
                    if (!reason) {
                        alert($t('Please provide a rejection reason.'));
                        return;
                    }
                    
                    var locationId = $('[name="location_id"]').val() || $('[name="location[location_id]"]').val();
                    
                    if (!locationId) {
                        alert($t('Unable to determine location ID.'));
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
                                self.closeModal();
                                // Reload the page to show the updated status
                                window.location.reload();
                            } else {
                                alert(response.message || $t('An error occurred while rejecting the location.'));
                            }
                        },
                        error: function (xhr, status, error) {
                            alert($t('An error occurred while rejecting the location.'));
                        }
                    });
                }
            }]
        };

        // Create modal content
        var modalContent = $('<div id="reject-modal-content">' +
            '<div class="field required">' +
            '<label for="rejection-reason" class="label">' +
            '<span>' + $t('Rejection Reason') + '</span>' +
            '</label>' +
            '<div class="control">' +
            '<textarea id="rejection-reason" name="rejection_reason" class="textarea" rows="5" cols="50" required="required"></textarea>' +
            '</div>' +
            '</div>' +
            '</div>');

        // Initialize modal
        var rejectModal = modal(options, modalContent);
        
        // Store modal reference globally
        window.dealerLocatorRejectModal = rejectModal;
        
        // Bind click event to reject button
        $(element).on('click', function (e) {
            e.preventDefault();
            modalContent.modal('openModal');
            return false;
        });
    };
});