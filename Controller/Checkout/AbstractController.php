<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

use Magento\Checkout\Controller\Express\RedirectLoginInterface;
use Magento\Framework\Exception\LocalizedException;

abstract class AbstractController extends \Magento\Framework\App\Action\Action implements RedirectLoginInterface
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->_customerUrl = $customerUrl;
        parent::__construct($context);
    }

    /**
     * @throws LocalizedException
     */
    protected function _validateQuote()
    {
        $quote = $this->checkoutSession->getQuote();

        if (!$quote->hasItems() || $quote->getHasError()) {
            throw new LocalizedException(__('Unable to initialize easyCredit Payment.'));
        }
    }

    /**
     * Returns a list of action flags [flag_key] => boolean
     * @return array
     */
    public function getActionFlagList()
    {
        return [];
    }

    /**
     * Returns before_auth_url redirect parameter for customer session
     * @return string|null
     */
    public function getCustomerBeforeAuthUrl()
    {
        return null;
    }

    /**
     * Returns login url parameter for redirect
     * @return string|null
     */
    public function getLoginUrl()
    {
        return $this->_customerUrl->getLoginUrl();
    }

    /**
     * Returns action name which requires redirect
     * @return string|null
     */
    public function getRedirectActionName()
    {
        return 'start';
    }
}
