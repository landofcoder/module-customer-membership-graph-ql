# Mage2 Module Lof CustomerMembershipGraphQl

    ``landofcoder/module-customer-membership-graph-ql``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Magento 2 Customer Membership Graph Ql

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Lof`
 - Enable the module by running `php bin/magento module:enable Lof_CustomerMembershipGraphQl`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require landofcoder/module-customer-membership-graph-ql`
 - enable the module by running `php bin/magento module:enable Lof_CustomerMembershipGraphQl`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - GraphQl Endpoint
	- MembershipProducts

 - GraphQl Endpoint
	- MyMembership

 - GraphQl Endpoint
	- CancelMembership

 - GraphQl Endpoint
	- MembershipTransaction


## Attributes

## Example Graph Ql Query

1. Query Membership Products:
```
{
  membershipProducts(
    filters: {}, 
    pageSize: 10, 
    currentPage: 1, 
    sort:{
      created_at: DESC
    }
  ){
    items{
      entity_id
      sku
      name
      status
      duration {
        record_id
        membership_duration
        membership_unit
        membership_price
        membership_order
        initialize
      }
      url_key
      customer_group
      featured_package
      short_description
      price
      final_price
      created_at
      store_id
    }
    total_count
  }
}
```

2. Query My Membership (should logged in customer account before):
```
{
	myMembership{
    membership_id
    group_id
    name
    duration
    price
    expiration_date
    created_at
    status
    product_id
    item_id
    product_options
  }
}
```

3. Query My Membership Transaction (should logged in customer account before):
```
{
	membershipTransaction(
    filters: {}, 
    pageSize: 10, 
    currentPage: 1, 
    sort:{
      created_at: DESC
    }
  ){
    items{
      	transaction_id
      	name
      	package
      	amount
       	duration
      	duration_unit
        created_at
      	product_id
        item_id
    }
    
    total_count
  }
}
```
4. Query Mutation Cancel Membership Request (should logged in customer account before):
```
mutation{
  cancelMembership(customer_comment: "I want cancel my membership!")
}
```

5. Query Mutation add membership product to cart (should logged in customer account before):

```
mutation {
  addMembershipProductsToCart(
        input: {
            cart_id: String
            cart_items: [{
              data : {
                sku: String,
                quantity: Int
              },
              duration: String!
            }]
        }
    ) {
        cart {
            items {
                ... on SimpleCartItem {
                    product {
                        name
                        sku
                    }
                    quantity
                    customizable_options {
                        label
                        type
                        values {
                            label
                            value
                            price {
                                value
                            }
                        }
                    }
                }
            }
        }
    }
}
```

``duration: String!``

example:

```
duration: "1|month"
```
