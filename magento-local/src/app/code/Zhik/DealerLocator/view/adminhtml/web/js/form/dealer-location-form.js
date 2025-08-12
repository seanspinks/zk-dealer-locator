/**
 * Copyright © Zhik. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/form',
    'jquery',
    'underscore',
    'mage/url'
], function (Form, $, _, urlBuilder) {
    'use strict';

    return Form.extend({
        defaults: {
            tracks: {
                canSave: true
            }
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            return this;
        },

        /**
         * Save form handler
         */
        save: function (redirect, data) {
            // Validate form before saving
            this.validate();
            
            if (!this.source.get('params.invalid')) {
                this.setAdditionalData(data).submit(redirect);
            } else {
                this.focusInvalid();
            }
            
            return this;
        },

        /**
         * Submit form
         */
        submit: function (redirect) {
            var formData = this.source.get('data');
            
            // Add form_key if not present
            if (!formData.form_key && window.FORM_KEY) {
                formData.form_key = window.FORM_KEY;
                this.source.set('data.form_key', window.FORM_KEY);
            }
            
            // Call parent submit
            return this._super(redirect);
        },

        /**
         * Validates each element and returns true, if all elements are valid.
         */
        validate: function (elem) {
            return this._super(elem);
        },

        /**
         * Show invalid fields
         */
        focusInvalid: function () {
            return this._super();
        }
    });
});