<?php

namespace ChannelPilot\Zed\CpExport\Business;

use ChannelPilot\Zed\CpExport\Business\Model\CpExport;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use ChannelPilot\Zed\CpExport\CpExportDependencyProvider;

/**
 * @method \ChannelPilot\Zed\CpExport\Persistence\CpExportQueryContainer getQueryContainer()
 */
class CpExportBusinessFactory extends AbstractBusinessFactory
{

    public function getCpExportQueryContainer()
    {
        return $this->getQueryContainer();
    }
    
    public function getLocaleFacade()
    {
        return $this->getProvidedDependency(CpExportDependencyProvider::FACADE_LOCALE);
    }
    
    public function getPriceProductFacade()
    {
        return $this->getProvidedDependency(CpExportDependencyProvider::FACADE_PRICE_PRODUCT);
    }
    
    public function getProductImageFacade()
    {
        return $this->getProvidedDependency(CpExportDependencyProvider::FACADE_PRODUCT_IMAGE);
    }
    
    public function getTaxFacade()
    {
        return $this->getProvidedDependency(CpExportDependencyProvider::FACADE_TAX);
    }
    
    public function getStockFacade()
    {
        return $this->getProvidedDependency(CpExportDependencyProvider::FACADE_STOCK);
    }

    public function getCategoryFacade()
    {
        return $this->getProvidedDependency(CpExportDependencyProvider::FACADE_CATEGORY);
    }

    public function createCpExport()
    {
        return new CpExport($this);
    }
}
