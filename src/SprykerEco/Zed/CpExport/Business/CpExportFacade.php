<?php

namespace SprykerEco\Zed\CpExport\Business;

use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \SprykerEco\Zed\CpExport\Business\CpExportBusinessFactory getFactory()
 */
class CpExportFacade extends AbstractFacade implements CpExportFacadeInterface
{

    public function getExportData()
    {
        return $this->getFactory()->createCpExport()->createExportData();
    }

}
