/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/prompt',
    'mage/translate'
], function ($, prompt, $t) {
    'use strict';

    return function (actionData, gridData) {
        prompt({
            title: $t('Reject Location'),
            content: $t('Please provide a reason for rejection:'),
            value: '',
            validation: true,
            validationRules: ['required-entry'],
            actions: {
                confirm: function (reason) {
                    // Add the reason to the action data
                    actionData.data = actionData.data || {};
                    actionData.data.reason = reason;
                    
                    // Let Magento's grid handle the actual request
                    gridData.actionHandler(actionData);
                }
            }
        });
        
        // Return false to prevent default action
        return false;
    };
});