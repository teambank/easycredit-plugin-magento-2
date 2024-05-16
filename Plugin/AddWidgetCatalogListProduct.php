<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;

class AddWidgetCatalogListProduct extends AddWidgetAbstract
{
    public function afterGetProductPrice(ListProduct $listProduct, $result, Product $product)
    {
        if (! $this->isActive) {
            return $result;
        }

        $widget = $listProduct->getLayout()
            ->createBlock('Netzkollektiv\EasyCredit\Block\Ui\Widget')
            ->setTemplate('Netzkollektiv_EasyCredit::easycredit/widget.phtml')
            ->setAmount($this->taxHelper->getTaxPrice($product, $product->getFinalPrice(), true))
            ->setAdditionalAttributes('display-type="minimal" extended="false"')
            ->setPosition('listing')
            ->toHtml();

        $result .= $widget;
        return $result;
    }
}
