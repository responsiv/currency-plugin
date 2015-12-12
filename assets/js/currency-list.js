/*
 * Scripts for the Currencies controller.
 */
+function ($) { "use strict";

    var CurrencyList = function() {

        this.clickRecord = function(recordId) {
            var newPopup = $('<a />')

            newPopup.popup({
                handler: 'onUpdateForm',
                extraData: {
                    'record_id': recordId,
                }
            })
        }

        this.createRecord = function() {
            var newPopup = $('<a />')
            newPopup.popup({ handler: 'onCreateForm' })
        }

    }

    $.currencyList = new CurrencyList;

}(window.jQuery);