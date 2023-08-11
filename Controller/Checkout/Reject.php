<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

class Reject extends AbstractController
{
    /**
     * Dispatch request
     */
    public function execute(): void
    {
        $this->messageManager->addErrorMessage(__('Unfortunately, easyCredit payment cannot be offered.'));
        $this->_redirect('checkout/cart');
    }

    /**
     * Returns action name which requires redirect
     */
    public function getRedirectActionName(): string
    {
        return 'reject';
    }
}
