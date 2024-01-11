define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'easycredit_installment_payment',
                component: 'Netzkollektiv_EasyCredit/js/view/payment/method-renderer/easycredit-method'
            }
        );
        rendererList.push(
            {
                type: 'easycredit_bill_payment',
                component: 'Netzkollektiv_EasyCredit/js/view/payment/method-renderer/easycredit-method'
            }
        );
        return Component.extend({});
    }
);