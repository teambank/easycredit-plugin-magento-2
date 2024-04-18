<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Netzkollektiv\EasyCredit\Model\Payment;

/**
 * Class EasyCredit Transaction Status
 */
class Status extends Column
{
    private Escaper $escaper;

    private CollectionFactory $paymentCollectionFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        CollectionFactory $paymentCollectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (! isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $ids = [];
        foreach ($dataSource['data']['items'] as $item) {
            if (! isset($item['payment_method'])) {
                continue;
            }

            if ($item['payment_method'] != Payment::CODE) {
                continue;
            }

            $ids[] = $item[$item['id_field_name']];
        }

        $collection = $this->paymentCollectionFactory->create()
            ->addFieldToSelect('parent_id')
            ->addFieldToSelect('additional_information')
            ->addFieldToFilter('parent_id', [
                'in' => $ids,
            ])->load();

        foreach ($dataSource['data']['items'] as &$item) {
            $paymentItem = $collection->getItemByColumnValue('parent_id', $item[$item['id_field_name']]);
            if ($paymentItem instanceof DataObject) {
                $transactionId = $this->escaper->escapeHtml(
                    $paymentItem->getData('additional_information/transaction_id')
                );
                $item[$this->getData('name')] = '<easycredit-merchant-status-widget 
                    tx-id="' . $transactionId . '" 
                    date="' . substr($item['created_at'], 0, strpos(' ', (string) $item['created_at'])) . '"></<easycredit-merchant-status-widget>
                ';
            }
        }

        return $dataSource;
    }
}
