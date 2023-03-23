<?php
/**
 * Copyright © landofcoder.com All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\CustomerMembershipGraphQl\Model\Resolver;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Message\AbstractMessage;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Lof\CustomerMembership\Helper\Data;
use Lof\CustomerMembership\Model\Product\Type\CustomerMembership;
use Lof\CustomerMembership\Model\ResourceModel\Membership as MembershipResource;
use Magento\Quote\Model\Cart\Data\CartItemFactory;
use Magento\QuoteGraphQl\Model\Cart\AddProductsToCart;
use Magento\Quote\Model\QuoteMutexInterface;

/**
 * Class AddMembershipProductsToCart
 * @package Lof\CustomerMembershipGraphQl\Model\Resolver
 */
class AddMembershipProductsToCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var AddProductsToCart
     */
    private $addProductsToCart;

    /**
     * @var QuoteMutexInterface
     */
    private $quoteMutex;

    /**
     * @var MembershipResource
     */
    private $resource;

    /**
     * AddMembershipProductsToCart constructor.
     * @param GetCartForUser $getCartForUser
     * @param Data $helperData
     * @param ProductRepositoryInterface $productRepository
     * @param CartRepositoryInterface $cartRepository
     * @param MembershipResource $resource
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        Data $helperData,
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $cartRepository,
        AddProductsToCart $addProductsToCart,
        QuoteMutexInterface $quoteMutex,
        MembershipResource $resource
    ) {
        $this->getCartForUser    = $getCartForUser;
        $this->helperData        = $helperData;
        $this->productRepository = $productRepository;
        $this->cartRepository    = $cartRepository;
        $this->addProductsToCart = $addProductsToCart;
        $this->quoteMutex = $quoteMutex;
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$this->helperData->isEnabled()) {
            throw new GraphQlNoSuchEntityException(__('Membership is disabled.'));
        }

        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        if (empty($args['input']['membership_input'])
            || !is_array($args['input']['membership_input'])
        ) {
            throw new GraphQlInputException(__('Required parameter "membership_input" is missing'));
        }

        return $this->quoteMutex->execute(
            [$args['input']['cart_id']],
            \Closure::fromCallable([$this, 'run']),
            [$context, $args]
        );
    }

    /**
     * Run the resolver.
     *
     * @param ContextInterface $context
     * @param array|null $args
     * @return array[]
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function run($context, ?array $args): array
    {
        $maskedCartId = $args['input']['cart_id'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart    = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $membershipInput = $args['input']['membership_input'];
        $sku             = $membershipInput['sku'];
        $optionId        = isset($membershipInput['option_id']) ? $membershipInput['option_id'] : '';
        $qty        = isset($membershipInput['qty']) ? (float)$membershipInput['qty'] : 1;
        $durationOption = $optionId;
        try {
            /** @var \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Could not find a product with SKU "%sku"', ['sku' => $sku]));
        }

        try {
            $selected_options = null;
            $entered_options = null;
            $cartItems = [];
            $cartItemData = [
                "data" => [
                    "sku" => $sku,
                    "quantity" => $qty,
                    "parent_sku" => null,
                    "selected_options" => $selected_options,
                    "entered_options" => $entered_options
                ]
            ];

            $cartItems[] = $cartItemData;
            $this->addProductsToCart->execute($cart, $cartItems);

            list($duration, $durationUnit) = explode('|', $durationOption);

            $durationOptions = $product->getData('duration');
            if (!is_array($durationOptions)) {
                $durationOptions = json_decode($durationOptions, true);
            }
            $packagePrice = 0;
            foreach ($durationOptions as $option) {
                if ($duration == $option['membership_duration'] && $durationUnit == $option['membership_unit']) {
                    $packagePrice = $option['membership_price'];
                }
            }

            $options['membership_duration'] = $duration;
            $options['membership_unit'] = $durationUnit;
            $options['customer_group'] = $product->getData('customer_group');
            $options['membership_price'] = $packagePrice;
            $duration = @serialize($options);

            $this->updateCartItemOption($product->getEntityId(), $cart, $duration);
        } catch (Exception $e) {
            throw new GraphQlInputException(
                __(
                    'Could not add the product %productId with SKU %sku to the shopping cart: %message',
                    ['productId' => $product->getEntityId(), 'sku' => $sku, 'message' => $e->getMessage()]
                )
            );
        }
        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }

    /**
     * update cart item option
     *
     * @param int $productId
     * @param \Magento\Quote\Model\Quote $cart
     * @param string $duration
     */
    private function updateCartItemOption($productId, $cart, $duration)
    {
        $cartItems = $cart->getAllVisibleItems();
        $currentQuoteItem = null;
        foreach ($cartItems as $item) {
            $currentProductId = $item->getProduct()->getId();
            if ($productId == $currentProductId) {
                $currentQuoteItem = $item;
                break;
            }
        }
        if ($currentQuoteItem && $duration) {
            $this->resource->updateQuoteItemOption($currentQuoteItem->getId(), $productId, $duration);
        }
    }

    /**
     * Collecting cart errors
     *
     * @param Quote $cart
     * @return string
     */
    private function getCartErrors(Quote $cart): string
    {
        $errorMessages = [];

        /** @var AbstractMessage $error */
        foreach ($cart->getErrors() as $error) {
            $errorMessages[] = $error->getText();
        }

        return implode(PHP_EOL, $errorMessages);
    }
}
