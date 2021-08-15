<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\CustomerMembershipGraphQl\Model\Resolver\DataProvider;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Lof\CustomerMembership\Api\ProductMembershipRepositoryInterface;

class MyMembership
{

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var ProductMembershipRepositoryInterface
     */
    protected $productMembershipRepository;

    /**
     * MyMembership constructor.
     * @param ProductMembershipRepositoryInterface $productMembershipRepositoryInterface
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        ProductMembershipRepositoryInterface $productMembershipRepositoryInterface,
        GetCustomer $getCustomer
    )
    {
        $this->productMembershipRepository = $productMembershipRepositoryInterface;
        $this->getCustomer = $getCustomer;
    }

    /**
     * @inheritdoc
     */
    public function getMyMembership($args, $context)
    {
        $customer = $this->getCustomer->execute($context);
        $store = $context->getExtensionAttributes()->getStore();
        return $this->productMembershipRepository->getByCustomer($customer->getId(), $store->getId());
    }
}

