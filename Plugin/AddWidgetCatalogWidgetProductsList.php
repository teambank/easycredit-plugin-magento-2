<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Plugin;

use Magento\Catalog\Model\Product;
use Magento\CatalogWidget\Block\Product\ProductsList;

class AddWidgetCatalogWidgetProductsList extends AddWidgetAbstract
{
    public function afterGetProductPriceHtml(ProductsList $productsList, $result, Product $product)
    {
        if (! $this->isActive) {
            return $result;
        }

        $widget = $productsList->getLayout()
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
