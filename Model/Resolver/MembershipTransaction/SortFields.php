<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\CustomerMembershipGraphQl\Model\Resolver\MembershipTransaction;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Retrieves the sort fields data
 */
class SortFields implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $sortFieldsOptions = [
            ['label' => "transaction_id", 'value' => "transaction_id"],
            ['label' => "created_at", 'value' => "created_at"],
            ['label' => "updated_at", 'value' => "updated_at"],
            ['label' => "package", 'value' => "package"],
            ['label' => "status", 'value' => "status"],
            ['label' => "amount", 'value' => "amount"],
            ['label' => "duration", 'value' => "duration"],
            ['label' => "duration_unit", 'value' => "duration_unit"],
            ['label' => "product_id", 'value' => "product_id"],
            ['label' => "group_id", 'value' => "group_id"]
        ];
        
        $data = [
            'default' => "created_at",
            'options' => $sortFieldsOptions,
        ];

        return $data;
    }
}
