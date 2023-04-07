<?php
namespace Lof\CustomerMembershipGraphQl\Plugin;

use Magento\Catalog\Model\Product as CatalogProduct;
use Lof\CustomerMembership\Model\Product\Type\CustomerMembership;

class Product
{
    /**
     * @var \Lof\CustomerMembership\Helper\Data
     */
    private $helperData;

    /**
     * @var bool
     */
    protected $_flagChecked = false;

    /**
     * Initialize dependencies.
     *
     * @param \Lof\CustomerMembership\Helper\Data $helperData
     */
    public function __construct(
        \Lof\CustomerMembership\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param CatalogProduct $subject
     * @param mixed $result
     * @return bool|mixed
     */
    public function afterIsSalable(CatalogProduct $subject, $result)
    {
        if ($this->helperData->isEnabled() && $subject->getTypeId() == CustomerMembership::TYPE_CODE) {
            $result = true;
        }
        return $result;
    }

    /**
     * @param CatalogProduct $subject
     * @param mixed $result
     * @return bool|mixed
     */
    public function afterIsAvailable(CatalogProduct $subject, $result)
    {
        if ($this->helperData->isEnabled() && $subject->getTypeId() == CustomerMembership::TYPE_CODE) {
            $result = true;
        }
        return $result;
    }
}
