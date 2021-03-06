<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\CustomerMembershipGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\Query;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Lof\CustomerMembership\Api\ProductMembershipRepositoryInterface;
use Lof\CustomerMembership\Helper\Data;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Filter;

class MembershipProducts
{
/**
     * @var string
     */
    private const SPECIAL_CHARACTERS = '-+~/\\<>\'":*$#@()!,.?`=%&^';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var ProductMembershipRepositoryInterface
     */
    private $apiRepository;

    /**
     * @var Data
     */
    protected $helperData;

    public function __construct(
        ProductMembershipRepositoryInterface $apiRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScopeConfigInterface $scopeConfig,
        Data $helperData
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->apiRepository = $apiRepositoryInterface;
        $this->scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
    }
    /**
     * @inheritdoc
     */
    public function getMembershipProducts($args, $context)
    {
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        /* if(isset($args['filters']) && (!isset($args['filters']['status']) || !$args['filters']['status'])){
            $args['filters']['status'] = ['eq' => 1];
        } */
        $store = $context->getExtensionAttributes()->getStore();
        $args[Filter::ARGUMENT_NAME] = $this->formatMatchFilters($args['filters'], $store);
        $searchCriteria = $this->searchCriteriaBuilder->build( 'membershipProducts', $args );
        $searchCriteria->setCurrentPage( $args['currentPage'] );
        $searchCriteria->setPageSize( $args['pageSize'] );

        $searchResult = $this->apiRepository->getList( $searchCriteria );
        $totalPages = $args['pageSize'] ? ((int)ceil($searchResult->getTotalCount() / $args['pageSize'])) : 0;
        $resultItems = $searchResult->getItems();
        $items = [];
        if($resultItems){
            foreach($resultItems as $_item){
                $_new_item = $_item->__toArray();
                $duration = isset($_new_item['duration'])?$_new_item['duration']:[];
                $duration = $this->helperData->getDurationDecoded($duration);
                $new_duration = [];
                if ($duration && count($duration)) {
                    foreach ($duration as $_duration_item) {
                        $new_duration[] = [
                            'record_id' => (int)$_duration_item['record_id'],
                            'membership_duration' => (int)$_duration_item['membership_duration'],
                            'membership_unit' => $_duration_item['membership_unit'],
                            'membership_price' => (float)$_duration_item['membership_price'],
                            'membership_order' => (int)$_duration_item['membership_order'],
                            'initialize' => (int)$_duration_item['initialize']
                        ];
                    }
                }
                $_new_item['duration'] = $new_duration;
                $items[] = $_new_item;
            }
        }
        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $items,
            'page_info' => [
                'page_size' => $args['pageSize'],
                'current_page' => $args['currentPage'],
                'total_pages' => $totalPages
            ]
        ];
    }
    /**
     * Format match filters to behave like fuzzy match
     *
     * @param array $filters
     * @param StoreInterface $store
     * @return array
     * @throws InputException
     */
    private function formatMatchFilters(array $filters, StoreInterface $store): array
    {
        $minQueryLength = $this->scopeConfig->getValue(
            Query::XML_PATH_MIN_QUERY_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $availableMatchFilters = ["store_id"];
        foreach ($filters as $filter => $condition) {
            $conditionType = current(array_keys($condition));
            $tmpminQueryLength = $minQueryLength;
            if(in_array($filter, $availableMatchFilters)){
                $tmpminQueryLength = 1;
            }
            if ($conditionType === 'match') {
                $searchValue = trim(str_replace(self::SPECIAL_CHARACTERS, '', $condition[$conditionType]));
                $matchLength = strlen($searchValue);
                if ($matchLength < $tmpminQueryLength) {
                    throw new InputException(__('Invalid match filter. Minimum length is %1.', $tmpminQueryLength));
                }
                unset($filters[$filter]['match']);
                if($filter == "store_id"){
                    $searchValue = (int)$searchValue;
                }
                $filters[$filter]['like'] = '%' . $searchValue . '%';
            }
        }
        return $filters;
    }
}

