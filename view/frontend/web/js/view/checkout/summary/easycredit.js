define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils',
        'jquery'
    ],
    function (Component, quote, totals, $) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    template: 'Netzkollektiv_EasyCredit/checkout/summary/easycredit'
                },
                isIncludedInSubtotal: window.checkoutConfig.isIncludedInSubtotal,
                totals: totals.totals,

                /**
                 * @returns {Number}
                 */
                getEasyCreditSegment: function () {
                    var easycredit = totals.getSegment('easycredit') || totals.getSegment('easycredit');

                    if (easycredit !== null && easycredit.hasOwnProperty('value')) {
                        return easycredit.value;
                    }

                    return 0;
                },

                /**
                 * Get interest value
                 *
                 * @returns {String}
                 */
                getValue: function () {
                    return this.getFormattedPrice(this.getEasyCreditSegment());
                },

                /**
                 * Weee display flag
                 *
                 * @returns {Boolean}
                 */
                isDisplayed: function () {
                    return this.isFullMode() && this.getEasyCreditSegment() > 0;
                }
            }
        );
    }
);
