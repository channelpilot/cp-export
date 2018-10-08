<?php

namespace SprykerEco\Zed\CpExport\Persistence;

use Spryker\Zed\Kernel\Persistence\AbstractQueryContainer;

/**
 * @method \SprykerEco\Zed\CpExport\Persistence\CpExportPersistenceFactory getFactory()
 */
class CpExportQueryContainer extends AbstractQueryContainer implements CpExportQueryContainerInterface
{

    /**
     * @return \Orm\Zed\Product\Persistence\SpyProductQuery
     */
    public function queryProduct()
    {
        $query = $this->getFactory()->createProductQuery();

        return $query;
    }

    /**
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractQuery
     */
    public function queryProductAbstract()
    {
        $query = $this->getFactory()->createProductAbstractQuery();

        return $query;
    }

    /**
     *
     * @param int $idProductAbstract
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractLocalizedAttributesQuery
     */
    public function queryProductAbstractLocalizedAttributes($idProductAbstract)
    {
        $query = $this->getFactory()->createProductAbstractLocalizedAttributesQuery();
        $query->filterByFkProductAbstract($idProductAbstract);

        return $query;
    }

    /**
     *
     * @param int $idProduct
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductLocalizedAttributesQuery
     */
    public function queryProductLocalizedAttributes($idProduct)
    {
        $query = $this->getFactory()->createProductLocalizedAttributesQuery();
        $query->filterByFkProduct($idProduct);

        return $query;
    }

    /**
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractQuery
     */
    public function queryAbstractProductCategories()
    {
        $query = $this->getFactory()->createProductAbstractQuery();
        $query->leftJoinWithSpyProductCategory();

        return $query;
    }

    /**
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryCategoryNodeQuery()
    {
        $query = $this->getFactory()->createCategoryNodeQuery();

        return $query;
    }

}
