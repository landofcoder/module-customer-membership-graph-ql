<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\CustomerMembershipGraphQl\Model\Resolver\CancelMembership;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

class Identity implements IdentityInterface
{

    private $cacheTag = \Magento\Framework\App\Config::CACHE_TAG;

    /**
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData)
    {
        $ids =  empty($resolvedData['entity_id']) ?
                        [] : [$this->cacheTag, sprintf('%s_%s', $this->cacheTag, $resolvedData['entity_id'])];
        
                    return $ids;
    }
}

