<?php

namespace SprykerEco\Zed\CpExport;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

class CpExportDependencyProvider extends AbstractBundleDependencyProvider
{
    const FACADE_LOCALE = 'FACADE_LOCALE';
    const FACADE_PRICE_PRODUCT = 'FACADE_PRICE_PRODUCT';
    const FACADE_PRODUCT_IMAGE = 'FACADE_PRODUCT_IMAGE';
    const FACADE_TAX = 'FACADE_TAX';
    const FACADE_STOCK = 'FACADE_STOCK';
    const FACADE_CATEGORY = 'FACADE_CATEGORY';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container[self::FACADE_LOCALE] = function (Container $container) {
            return $container->getLocator()
                ->locale()
                ->facade();
        };

        $container[self::FACADE_PRICE_PRODUCT] = function (Container $container) {
            return $container->getLocator()
                ->priceProduct()
                ->facade();
        };

        $container[self::FACADE_PRODUCT_IMAGE] = function (Container $container) {
            return $container->getLocator()
                ->productImage()
                ->facade();
        };

        $container[self::FACADE_TAX] = function (Container $container) {
            return $container->getLocator()
                ->tax()
                ->facade();
        };

        $container[self::FACADE_STOCK] = function (Container $container) {
            return $container->getLocator()
                ->stock()
                ->facade();
        };

        $container[self::FACADE_CATEGORY] = function (Container $container) {
            return $container->getLocator()
                ->category()
                ->facade();
        };

        return $container;
    }
}
