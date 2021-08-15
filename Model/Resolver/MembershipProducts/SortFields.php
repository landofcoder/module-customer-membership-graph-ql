<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\CustomerMembershipGraphQl\Model\Resolver\MembershipProducts;

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
            ['label' => "entity_id", 'value' => "entity_id"],
            ['label' => "created_at", 'value' => "created_at"],
            ['label' => "updated_at", 'value' => "updated_at"],
            ['label' => "featured_package", 'value' => "featured_package"],
            ['label' => "status", 'value' => "status"],
            ['label' => "name", 'value' => "name"],
            ['label' => "sku", 'value' => "sku"],
            ['label' => "duration", 'value' => "duration"],
            ['label' => "customer_group", 'value' => "customer_group"],
            ['label' => "membership_order", 'value' => "membership_order"]
        ];
        
        $data = [
            'default' => "membership_order",
            'options' => $sortFieldsOptions,
        ];

        return $data;
    }
}
