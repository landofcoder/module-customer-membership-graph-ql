<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\CustomerMembershipGraphQl\Model\Resolver\DataProvider;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Lof\CustomerMembership\Api\CancelrequestRepositoryInterface;
use Lof\CustomerMembership\Api\MembershipRepositoryInterface;
use Lof\CustomerMembership\Model\CancelrequestFactory;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\GraphQl\Model\Query\ContextInterface;

class CancelMembership
{
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var CancelrequestRepositoryInterface
     */
    protected $cancelRepository;

    /**
     * @var CancelrequestFactory
     */
    protected $cancelrequestFactory;

    /**
     * @var MembershipRepositoryInterface
     */
    protected $membershipRepository;

    /**
     * MyMembership constructor.
     * @param CancelrequestRepositoryInterface $cancelRepositoryInterface
     * @param GetCustomer $getCustomer
     * @param CancelrequestFactory $cancelrequestFactory
     * @param MembershipRepositoryInterface $membershipRepository
     */
    public function __construct(
        CancelrequestRepositoryInterface $cancelRepositoryInterface,
        GetCustomer $getCustomer,
        CancelrequestFactory $cancelrequestFactory,
        MembershipRepositoryInterface $membershipRepository
    )
    {
        $this->cancelRepository = $cancelRepositoryInterface;
        $this->getCustomer = $getCustomer;
        $this->cancelrequestFactory = $cancelrequestFactory;
        $this->membershipRepository = $membershipRepository;
    }

    /**
     * @inheritdoc
     */
    public function getCancelMembership($args, $context)
    {
        $customer = $this->getCustomer->execute($context);
        $store = $context->getExtensionAttributes()->getStore();
        
        $currentMembership = $this->membershipRepository->getMyMembership($customer->getId());
        if (!$currentMembership) {
            throw new GraphQlNoSuchEntityException(__('The current customer isn\'t have membership plan.'));
        }
        $data = [
            'membership_id' => $currentMembership->getMembershipId(),
            'status' => \Lof\CustomerMembership\Model\Cancelrequest::PENDING,
            'customer_comment' => isset($args['customer_comment'])?$args['customer_comment']:"",
            'admin_comment' => '',
            'name' => $currentMembership->getName(),
            'duration' => $currentMembership->getDuration(),
            'price' => $currentMembership->getPrice(),
            'product_id' => $currentMembership->getProductId()
        ];

        $cancelRequestData = $this->cancelrequestFactory->create();
        $cancelRequestData->setData($data);

        $cancelRequest = $this->cancelRepository->saveByCustomer($customer->getId(), $cancelRequestData);
        return $cancelRequest->getId()?"Send cancel request is success.":"false";
    }
}

