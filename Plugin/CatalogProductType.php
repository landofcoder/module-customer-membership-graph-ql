<?php
namespace Lof\CustomerMembershipGraphQl\Plugin;

use Magento\CatalogGraphQl\Model\CatalogProductTypeResolver;
use Lof\CustomerMembership\Model\Product\Type\CustomerMembership;

class CatalogProductType
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
     * @param CatalogProductTypeResolver $subject
     * @param \Closure $proceed
     * @param array $data
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundResolveType(
        CatalogProductTypeResolver $subject,
        \Closure $proceed,
        array $data
    ) : string {

        if ($this->helperData->isEnabled()) {
            if (isset($data['type_id'])) {
                if ($data['type_id'] == 'customer_membership') {
                    return 'CustomerMembershipCartItem';
                }
            }
        }
        return $proceed($product);
    }
}
