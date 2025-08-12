define([
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/tabs',
    'mage/cookies'
], function ($) {
    'use strict';

    return function (config, element) {
        // Initialize tabs widget
        $(element).tabs({
            active: 0,
            collapsible: false,
            beforeActivate: function(event, ui) {
                // Update aria-selected attribute
                ui.oldTab.attr('aria-selected', 'false');
                ui.newTab.attr('aria-selected', 'true');
            }
        });

        // Handle delete action
        $(element).on('click', '.action.delete', function(e) {
            e.preventDefault();
            
            var deleteData = $(this).data('post');
            var confirmMessage = $(this).data('confirm');
            
            if (confirm(confirmMessage)) {
                var form = $('<form>', {
                    'action': deleteData.action,
                    'method': 'post'
                });
                
                // Add form key
                form.append($('<input>', {
                    'name': 'form_key',
                    'value': $.mage.cookies.get('form_key'),
                    'type': 'hidden'
                }));
                
                // Add any additional data
                $.each(deleteData.data, function(key, value) {
                    form.append($('<input>', {
                        'name': key,
                        'value': value,
                        'type': 'hidden'
                    }));
                });
                
                form.appendTo('body').submit();
            }
        });
    };
});