<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

class Cancel extends AbstractController
{

    /**
     * Dispatch request
     *
     * @return void
     */
    public function execute()
    {
        $this->messageManager->addErrorMessage(__('easyCredit payment was canceled.'));
        $this->_redirect('checkout/cart');
    }

    /**
     * Returns action name which requires redirect
     *
     * @return string|null
     */
    public function getRedirectActionName()
    {
        return 'cancel';
    }
}
