<?php

namespace SprykerEco\Zed\CpExport\Persistence;

use Orm\Zed\Category\Persistence\Base\SpyCategoryNodeQuery;
use Orm\Zed\Category\Persistence\Base\SpyCategoryQuery;
use Orm\Zed\Product\Persistence\Base\SpyProductAbstractQuery;
use Orm\Zed\ProductCategory\Persistence\Base\SpyProductCategoryQuery;
use Orm\Zed\Product\Persistence\SpyProductQuery;
use Orm\Zed\Product\Persistence\SpyProductAbstractLocalizedAttributesQuery;
use Orm\Zed\Product\Persistence\SpyProductLocalizedAttributesQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;

/**
 * @method \SprykerEco\Zed\CpExport\CpExportConfig getConfig()
 * @method \SprykerEco\Zed\CpExport\Persistence\CpExportQueryContainer getQueryContainer()
 */
class CpExportPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @return \Orm\Zed\Product\Persistence\SpyProductQuery
     */
    public function createProductQuery()
    {
        return SpyProductQuery::create();
    }

    /**
     * @return \Orm\Zed\Category\Persistence\SpyCategoryQuery
     */
    public function createCategoryQuery()
    {
        return SpyCategoryQuery::create();
    }

    /**
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractQuery
     */
    public function createProductAbstractQuery()
    {
        return SpyProductAbstractQuery::create();
    }

    /**
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function createCategoryNodeQuery()
    {
        return SpyCategoryNodeQuery::create();
    }

    /**
     * @return \Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery
     */
    public function createProductCategoryQuery()
    {
        return SpyProductCategoryQuery::create();
    }

    /**
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractLocalizedAttributesQuery
     */
    public function createProductAbstractLocalizedAttributesQuery()
    {
        return SpyProductAbstractLocalizedAttributesQuery::create();
    }

    /**
     * @return \Orm\Zed\Product\Persistence\SpyProductLocalizedAttributesQuery
     */
    public function createProductLocalizedAttributesQuery()
    {
        return SpyProductLocalizedAttributesQuery::create();
    }
}
