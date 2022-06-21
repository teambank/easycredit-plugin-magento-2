<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi;

use Magento\Store\Model\ScopeInterface;

class Logger implements \Netzkollektiv\EasyCreditApi\LoggerInterface
{
    protected $_logger;

    protected $debug = false;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;

        //if ($scopeConfig->getValue('payment/easycredit/debug_logging', ScopeInterface::SCOPE_STORE)) {
        $this->debug = true;
        //}
    }

    public function log($msg)
    {
        if (!$this->debug) {
            return;
        }

        return $this->info(
            $this->_format($msg)
        );
    }

    public function logDebug($msg)
    {
        if (!$this->debug) {
            return;
        }

        $this->_logger->debug(
            $this->_format($msg)
        );
        return $this;
    }

    public function logInfo($msg)
    {
        if (!$this->debug) {
            return;
        }

        $this->_logger->info(
            $this->_format($msg)
        );
        return $this;
    }

    public function logWarn($msg)
    {
        $this->_logger->warning(
            $this->_format($msg)
        );
        return $this;
    }

    public function logError($msg)
    {
        $this->_logger->error(
            $this->_format($msg)
        );
        return $this;
    }

    public function _format($msg)
    {
        if (is_array($msg) || is_object($msg)) {
            return print_r($msg, true);
        }
        return $msg;
    }
}
