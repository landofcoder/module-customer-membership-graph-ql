<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\CustomerMembershipGraphQl\Model\Resolver\DataProvider;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Lof\CustomerMembership\Api\MembershipRepositoryInterface;
use Lof\CustomerMembership\Helper\Data;

class MyMembership
{

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var MembershipRepositoryInterface
     */
    protected $membershipRepository;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * MyMembership constructor.
     * @param MembershipRepositoryInterface $membershipRepository
     * @param GetCustomer $getCustomer
     * @param Data $helperData
     */
    public function __construct(
        MembershipRepositoryInterface $membershipRepository,
        GetCustomer $getCustomer,
        Data $helperData
    )
    {
        $this->membershipRepository = $membershipRepository;
        $this->getCustomer = $getCustomer;
        $this->helperData = $helperData;
    }

    /**
     * @inheritdoc
     */
    public function getMyMembership($args, $context)
    {
        $customer = $this->getCustomer->execute($context);
        $store = $context->getExtensionAttributes()->getStore();
        return $this->membershipRepository->getMyMembership($customer->getId());
    }
}

