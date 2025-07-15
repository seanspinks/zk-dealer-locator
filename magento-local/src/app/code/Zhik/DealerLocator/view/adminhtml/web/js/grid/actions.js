/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/prompt',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function ($, prompt, confirm, $t) {
    'use strict';

    return {
        /**
         * Approve location
         * @param {String} url
         */
        approveLocation: function (url) {
            confirm({
                title: $t('Approve Location'),
                content: $t('Are you sure you want to approve this location?'),
                actions: {
                    confirm: function () {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            dataType: 'json',
                            showLoader: true,
                            data: {
                                form_key: window.FORM_KEY
                            },
                            success: function (response) {
                                if (response.success) {
                                    location.reload();
                                } else {
                                    alert(response.message || $t('An error occurred.'));
                                }
                            },
                            error: function () {
                                alert($t('An error occurred while approving the location.'));
                            }
                        });
                    }
                }
            });
        },

        /**
         * Reject location
         * @param {String} url
         */
        rejectLocation: function (url) {
            prompt({
                title: $t('Reject Location'),
                content: $t('Please provide a reason for rejection:'),
                value: '',
                validation: true,
                validationRules: ['required-entry'],
                actions: {
                    confirm: function (reason) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            dataType: 'json',
                            showLoader: true,
                            data: {
                                form_key: window.FORM_KEY,
                                reason: reason
                            },
                            success: function (response) {
                                if (response.success) {
                                    location.reload();
                                } else {
                                    alert(response.message || $t('An error occurred.'));
                                }
                            },
                            error: function () {
                                alert($t('An error occurred while rejecting the location.'));
                            }
                        });
                    }
                }
            });
        }
    };
});