<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\CustomerMembershipGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class MembershipProducts implements ResolverInterface
{

    private $membershipProductsDataProvider;

    /**
     * @param DataProvider\MembershipProducts $membershipProductsRepository
     */
    public function __construct(
        DataProvider\MembershipProducts $membershipProductsDataProvider
    ) {
        $this->membershipProductsDataProvider = $membershipProductsDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $membershipProductsData = $this->membershipProductsDataProvider->getMembershipProducts(
            $args,
            $context
        );
        return $membershipProductsData;
    }
}

