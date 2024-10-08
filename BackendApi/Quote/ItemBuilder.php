<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi\Quote;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Teambank\EasyCreditApiV3 as Api;

class ItemBuilder
{
    private CategoryResource $_categoryResource;

    private StoreManagerInterface $_storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryResource $categoryResource
    ) {
        $this->_categoryResource = $categoryResource;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param array $categoryIds
     * @return array|bool|null|string
     */
    private function getDeepestCategoryName($categoryIds)
    {
        if (is_array($categoryIds) && $categoryIds !== []) {
            $categoryId = end($categoryIds);
            return $this->_categoryResource->getAttributeRawValue(
                $categoryId,
                'name',
                $this->_storeManager->getStore()->getId()
            );
        }

        return null;
    }

    private function buildSkus($item): array
    {
        $skus = [];
        foreach (\array_filter(
            [
                'sku' => $item->getSku(),
                'ean' => $item->getEan(),
            ]
        ) as $type => $sku) {
            $skus[] = new Api\Model\ArticleNumberItem(
                [
                    'numberType' => $type,
                    'number' => $sku,
                ]
            );
        }

        return $skus;
    }

    public function build($item): Api\Model\ShoppingCartInformationItem
    {
        return new Api\Model\ShoppingCartInformationItem(
            [
                'productName' => $item->getName(),
                'productUrl' => $item->getProduct()->getProductUrl(),
                'productImageUrl' => $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $item->getProduct()->getSmallImage(),
                'quantity' => (int) $item->getQty(),
                'price' => $item->getPrice(),
                'manufacturer' => $item->getProduct()->getData('manufacturer'),
                'productCategory' => $this->getDeepestCategoryName($item->getProduct()->getCategoryIds()),
                'articleNumber' => $this->buildSkus($item),
            ]
        );
    }
}
