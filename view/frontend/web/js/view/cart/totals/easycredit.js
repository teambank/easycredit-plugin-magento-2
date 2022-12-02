define(
    [
        'Netzkollektiv_EasyCredit/js/view/checkout/summary/easycredit'
    ],
    function (Component) {
        'use strict';

        return Component.extend(
            {
                /**
                 * @override
                 */
                isFullMode: function () {
                    return true;
                }
            }
        );
    }
);
