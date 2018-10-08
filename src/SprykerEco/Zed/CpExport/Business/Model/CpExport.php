<?php

namespace SprykerEco\Zed\CpExport\Business\Model;

use SprykerEco\Zed\CpExport\Business\CpExportBusinessFactory;
use SprykerEco\Shared\ChannelPilot\ChannelPilotConstants;

class CpExport
{
    protected $cpExportBusinessFactory;
    protected $cpExportQueryContainer;
    protected $localeTransfer;

    const XML_FILE_OPEN = '<?xml version="1.0" encoding="utf-8"?><root><catalog>';
    const XML_FILE_CLOSE = '</catalog></root>';
    const XML_PRODUCT_OPEN_TAG = '<product>';
    const XML_PRODUCT_CLOSE_TAG = '</product>';

    private $classmap = array(
        'fk_product_abstract' => 'fk_product_abstract',
        'id_product_attributes' => 'id_product_attributes',
        'fk_product' => 'fk_product',
        'fk_locale' => 'fk_locale',
        'id_price_product' => 'id_price_product',
        'price_type_name' => 'price_type_name',
        'id_entity' => 'id_entity',
        'fk_currency' => 'fk_currency',
        'fk_store' => 'fk_store',
        'id_currency' => 'id_currency',
        'sku_product' => 'sku_product',
        'sku_product_abstract' => 'sku_product_abstract',
        'id_product_abstract' => 'id_product_abstract',
        'fk_tax_set' => 'fk_tax_set',
        'fk_price_type' => 'fk_price_type',
        'price_type' => 'price_type',
        'id_abstract_attributes' => 'id_abstract_attributes'
    );

    private $attributesIgnoreListMap = array(
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'bundled_product' => 'bundled_product'
    );

    private $pricesIgnoreListMap = array(
        'id_product' => 'id_product',
        'is_active' => 'is_active',
        'price_dimension' => 'price_dimension'
    );

    private $priceCodeIgnoreMap = array(
        'CHF' => 'CHF'
    );

    private $taxIgnoreMap = array(
        'country' => 'country',
        'rate' => 'rate'
    );

    private $imagesIgnoreListMap = array(
        'id_product' => 'id_product',
        'sort_order' => 'sort_order'
    );

    private $stockIgnoreListMap = array(
        'sku' => 'sku',
        'stock_type' => 'stock_type',
        'fk_stock' => 'fk_stock',
        'id_stock_product' => 'id_stock_product'
    );

    private $localeIgnoreMap = array(
        'de_DE' => 'de_DE'
    );


    public function __construct(CpExportBusinessFactory $cpExportBusinessFactory)
    {
        $this->cpExportBusinessFactory = $cpExportBusinessFactory;
    }

    public function createExportData()
    {
        $this->cpExportQueryContainer = $this->cpExportBusinessFactory->getCpExportQueryContainer();
        $sprykerConcreteProducts = $this->getSprykerConcreteProducts();
        $cpProductsXml = self::XML_FILE_OPEN;
        $this->setCurrentLocale();

        foreach ($sprykerConcreteProducts as $key => $sprykerConcreteProduct) {
            $cpProduct = array();
            $sprykerAbstractProduct = $this->getSprykerAbstractProductByConcreteProduct($sprykerConcreteProduct);
            $cpProduct['parent_id'] = $sprykerConcreteProduct->getFkProductAbstract();
            $cpProduct['parent_sku'] = $sprykerAbstractProduct[0]->getSku();
            $cpProduct = $this->prepareProductDataForPlainXml(
                $cpProduct,
                $sprykerConcreteProduct->toArray(),
                null, null);
            $cpProduct = $this->prepareProductDataForPlainXml(
                $cpProduct,
                json_decode(str_replace("\\", "", $sprykerAbstractProduct[0]->getAttributes())),
                null, null);
            $cpProduct = $this->prepareProductAbstractLocalizedAttributes($cpProduct);
            $cpProduct = $this->prepareProductLocalizedAttributes($cpProduct);
            $cpProduct = $this->prepareProductPrices($cpProduct, $sprykerConcreteProduct);
            //Need to be Fixed:
            //$cpProduct = $this->prepareTax($cpProduct, $sprykerAbstractProduct[0]->getFkTaxSet());
            $cpProduct = $this->prepareProductImagesSetCollection($cpProduct, $sprykerConcreteProduct->getIdProduct());
            $cpProduct = $this->prepareProductStock($cpProduct, $sprykerConcreteProduct->getIdProduct());
            $cpProduct['category'] = $this->getCategoryTreesFor($sprykerConcreteProduct->getFkProductAbstract());
            $cpProductXml = $this->productToXml($cpProduct);
            $cpProductsXml = $cpProductsXml . $cpProductXml;

        }
        $cpProductsXml = $cpProductsXml . self::XML_FILE_CLOSE;

        return $cpProductsXml;
    }

    protected function getCategoryTreesFor($idProductAbstract)
    {
        $categories = $this->cpExportQueryContainer->queryAbstractProductCategories()->findByIdProductAbstract($idProductAbstract);
        $categoryTree = "";
        foreach ($categories->toArray()[0]['SpyProductCategories'] as $category) {
            $newCategory = "";
            $newCategory = $this->makeCategories($category['FkCategory'], $newCategory);
            if ($categoryTree !== "") {
                $categoryTree .= ChannelPilotConstants::EXPORT_CATEGORY_TREE_SEPARATOR;
            }
            $categoryTree .= substr($newCategory, 0, strlen($newCategory) - 1);
        }
        return $categoryTree;
    }

    protected function makeCategories($idNodeCategory, $newCat)
    {
        $categoryFacade = $this->cpExportBusinessFactory->getCategoryFacade();
        $newCat = $categoryFacade->read($idNodeCategory)['categoryKey'] . ChannelPilotConstants::EXPORT_CATEGORY_SEPARATOR . $newCat;
        $categoryNodeQuery = $this->cpExportBusinessFactory->getCpExportQueryContainer()->queryCategoryNodeQuery();
        $parentNodeId = $categoryNodeQuery->findPk($idNodeCategory)->getFkParentCategoryNode();
        if ($parentNodeId == "") {
            return $newCat;
        }
        $newCat = $this->makeCategories($parentNodeId, $newCat);
        return $newCat;
    }

    protected function getSprykerConcreteProducts()
    {
        $this->cpExportQueryContainer = $this->cpExportBusinessFactory->getCpExportQueryContainer();
        $concreteProductQuery = $this->cpExportQueryContainer->queryProduct();
        return $concreteProductQuery->find();
    }

    protected function getSprykerAbstractProductByConcreteProduct($sprykerConcretProduct)
    {
        $abstractProductQuery = $this->cpExportQueryContainer->queryProductAbstract();
        $abstractProductQuery->filterByIdProductAbstract($sprykerConcretProduct->getFkProductAbstract());
        return $abstractProductQuery->find();
    }

    protected function prepareProductDataForPlainXml($cpProduct, $arrayData, $ignoreList, $localeName)
    {
        $ignoreList = $this->getFullIgnoreList($ignoreList);

        foreach ($arrayData as $key => $data) {
            switch (strtolower($key)) {
                case array_key_exists($key, $ignoreList):
                    break;
                case 'attributes':
                    $cpProduct = $this->prepareProductDataForPlainXml($cpProduct, json_decode(str_replace("\\", "", $data)), null, null);
                    break;
                case 'money_value':
                    $cpProduct = $this->prepareProductDataForPlainXml($cpProduct, $data, null, $data['currency']['code']);
                    break;
                case 'currency':
                    $cpProduct = $this->prepareProductDataForPlainXml($cpProduct, $data, null, $data['code']);
                    break;
                default:
                    $key = $this->getFullKey($key, $localeName);
                    $cpProduct[$key] = $data;
                    break;
            }
        }
        return $cpProduct;
    }

    protected function getFullIgnoreList($ignoreList)
    {
        if ($ignoreList !== null) return array_merge($this->classmap, $ignoreList);
        return $this->classmap;
    }

    protected function getFullKey($key, $localeName)
    {
        if ($localeName !== null) return $key . "_" . $localeName;
        return $key;
    }

    protected function prepareProductAbstractLocalizedAttributes($cpProduct)
    {
        $sprykerProductAbstractLocalizedAttributes = $this->getProductAbstractLocalizedAttributes($cpProduct);

        foreach ($sprykerProductAbstractLocalizedAttributes as $key => $sprykerProductAbstractLocalizedAttribute) {
            $fkLocale = $this->localeTransfer->getLocaleName();
            if (!array_key_exists($fkLocale, $this->localeIgnoreMap)) {
                $cpProduct = $this->prepareProductDataForPlainXml(
                    $cpProduct,
                    $sprykerProductAbstractLocalizedAttribute->toArray(),
                    $this->attributesIgnoreListMap,
                    $fkLocale);
            }
        }
        return $cpProduct;
    }

    protected function prepareProductLocalizedAttributes($cpProduct)
    {
        $sprykerProductLocalizedAttributes = $this->getProductLocalizedAttributes($cpProduct);

        foreach ($sprykerProductLocalizedAttributes as $key => $sprykerProductLocalizedAttribute) {
            $fkLocale = $this->localeTransfer->getLocaleName();
            if (!array_key_exists($fkLocale, $this->localeIgnoreMap)) {
                $cpProduct = $this->prepareProductDataForPlainXml(
                    $cpProduct,
                    json_decode(str_replace("\\", "", $sprykerProductLocalizedAttribute->toArray()['attributes'])),
                    $this->attributesIgnoreListMap,
                    $fkLocale);
            }
        }
        return $cpProduct;
    }

    protected function getProductAbstractLocalizedAttributes($cpProduct)
    {
        $idProductAbstract = $cpProduct['parent_id'];
        $productAbstractLocalizedAttributesQuery = $this->cpExportQueryContainer->queryProductAbstractLocalizedAttributes($idProductAbstract);
        return $productAbstractLocalizedAttributesQuery->find();
    }

    protected function getProductLocalizedAttributes($cpProduct)
    {
        $idProduct = $cpProduct['id_product'];
        $productLocalizedAttributesQuery = $this->cpExportQueryContainer->queryProductLocalizedAttributes($idProduct);
        return $productLocalizedAttributesQuery->find();
    }

    protected function getLocaleNameBy($sprykerProductLocalizedAttribute)
    {
        $localeFacade = $this->cpExportBusinessFactory->getLocaleFacade();
        $locale = $localeFacade->getLocaleById($sprykerProductLocalizedAttribute->toArray()["fk_locale"]);
        return $locale->getLocaleName();
    }

    protected function setCurrentLocale()
    {
        $this->localeTransfer = $this->cpExportBusinessFactory->getLocaleFacade()->getCurrentLocale();
    }

    protected function prepareProductPrices($cpProduct, $sprykerConcretProduct)
    {
        $sprykerProductPrices = $this->getProductPrices($sprykerConcretProduct->getIdProduct(), $sprykerConcretProduct->getFkProductAbstract());

        foreach ($sprykerProductPrices as $sprykerProductPrice) {
            if (!array_key_exists($sprykerProductPrice->getMoneyValue()->getCurrency()->getCode(), $this->priceCodeIgnoreMap))
                $cpProduct = $this->prepareProductDataForPlainXml(
                    $cpProduct,
                    $sprykerProductPrice->toArray(),
                    $this->pricesIgnoreListMap,
                    null);
        }

        return $cpProduct;
    }

    protected function getProductPrices($idProductConcrete, $idProductAbstract)
    {
        $priceProductFacade = $this->cpExportBusinessFactory->getPriceProductFacade();
        return $priceProductFacade->findProductConcretePrices($idProductConcrete, $idProductAbstract);

    }

    //Need to be Fixed
    protected function prepareTax($cpProduct, $taxId)
    {
        $sprykerTaxRates = $this->getTaxById($taxId);
        echo '<pre>';
        print_r($sprykerTaxRates);
        echo '</pre>';

    }

    //Need to be Fixed
    protected function getTaxById($taxId)
    {
        $taxFacade = $this->cpExportBusinessFactory->getTaxFacade();
        return $taxFacade->getTaxRates();
    }

    protected function prepareProductImagesSetCollection($cpProduct, $idProduct)
    {
        $sprykerProductImagesSetCollection = $this->getProductImagesSetCollectionByProductId($idProduct);

        foreach ($sprykerProductImagesSetCollection as $key => $sprykerProductImagesSet) {
            $cpProduct = $this->prepareProductDataForPlainXml(
                $cpProduct,
                $sprykerProductImagesSet->toArray()['product_images'][0],
                $this->imagesIgnoreListMap,
                null);

        }
        return $cpProduct;
    }

    protected function getProductImagesSetCollectionByProductId($idProduct)
    {
        $productImageFacade = $this->cpExportBusinessFactory->getProductImageFacade();
        return $productImageFacade->getProductImagesSetCollectionByProductId($idProduct);
    }

    protected function prepareProductStock($cpProduct, $idProductConcrete)
    {
        $sprykerStocks = $this->getStockProductsByIdProduct($idProductConcrete);
        $stockTotal = 0;
        foreach ($sprykerStocks as $key => $sprykerStock) {
            $stockTotal += $sprykerStock->toArray()['quantity'];
            $cpProduct = $this->prepareProductDataForPlainXml(
                $cpProduct,
                $sprykerStock->toArray(),
                $this->stockIgnoreListMap,
                $sprykerStock->toArray()['stock_type']);
        }
        $cpProduct['quantity_total'] = $stockTotal;
        return $cpProduct;
    }

    protected function getStockProductsByIdProduct($idProductConcrete)
    {
        $stockFacade = $this->cpExportBusinessFactory->getStockFacade();
        return $stockFacade->getStockProductsByIdProduct($idProductConcrete);
    }

    protected function productToXml($cpProduct)
    {
        $cpProductXml = self::XML_PRODUCT_OPEN_TAG;
        foreach ($cpProduct as $key => $value) {
            $key = $this->getValidXmlTag($key);
            if ($value != "") {
                $cpProductXml = $cpProductXml . '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>';
            }
        }
        $cpProductXml = $cpProductXml . self::XML_PRODUCT_CLOSE_TAG;

        return $cpProductXml;
    }

    protected function getValidXmlTag($key)
    {
        if (is_numeric(substr($key, 0, 1))) return '_' . $key;
        return $key;
    }

}