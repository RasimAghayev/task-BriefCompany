# The API of product-catalog-api

Every endpoint, with its request, its response and the rules that hold for
it. The base path of the API is `/api`.

## The shape of a response

A successful answer carries the flag `success`, a message and the payload
under the key `data`:

```json
{
  "success": true,
  "message": "Product retrieved successfully.",
  "data": { }
}
```

A failed answer carries the flag `success` set to `false` and a message:

```json
{
  "success": false,
  "message": "Product not found."
}
```

A request that does not pass the validation is answered with the status 422
and the errors of every field:

```json
{
  "success": false,
  "message": "Validator",
  "data": {
    "categories.1": ["One of the categories does not exist."]
  }
}
```

## The authentication

The reads are open. The writes ask for a token of Sanctum in the header:

```
Authorization: Bearer <token>
```

A token is issued by the command `php artisan api:token <email>`, which
creates the user when it does not exist yet.

A request without a token is answered with the status 401:

```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

## The listing of the categories

### `GET /api/category`

**Parameters**

| Parameter | Type | Default | Meaning |
|---|---|---|---|
| `per_page` | integer | 15 | The number of rows of a page, at most 100. |
| `page` | integer | 1 | The page of the listing. |

**Response**

```json
{
  "success": true,
  "message": "Category retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Books",
      "parent_id": null,
      "created_at": "10/21/2026",
      "updated_at": "10/21/2026"
    }
  ],
  "links": {
    "first": "http://localhost:8080/api/category?page=1",
    "last": "http://localhost:8080/api/category?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "from": 1,
    "to": 1,
    "total": 1
  }
}
```

## One category

### `GET /api/category/{id}`

The status 200 with the category, or the status 404 with
`"message": "Category not found."`.

### `POST /api/category`

**Body**

| Field | Type | Rule |
|---|---|---|
| `name` | string | Required, at most 100 characters. |
| `parent_id` | integer | Optional. Has to be the id of an existing category. |

**Response:** the status 201 with the created category.

### `PUT /api/category/{id}`

The same body as the creation. The status 200 with the changed category, or
the status 404.

### `DELETE /api/category/{id}`

The status 200 when the category was deleted, the status 404 when it does not
exist, and the status 404 with `"message": "Category used other product."`
when a product still belongs to it.

## The listing of the products

### `GET /api/product`

The same parameters as the listing of the categories.

**Response**

```json
{
  "success": true,
  "message": "Product retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "A book of tests",
      "priceFrom": 10.5,
      "priceTo": 20.25,
      "price": "10.5-20.25",
      "published": "Yes",
      "status": "Active",
      "created_at": "2026-10-21 05:06:49",
      "updated_at": "2026-10-21 05:06:49",
      "categories": [
        { "category_id": 1, "category_name": "Books" },
        { "category_id": 2, "category_name": "Novels" }
      ]
    }
  ],
  "links": { },
  "meta": { }
}
```

| Field | Meaning |
|---|---|
| `price` | The two prices as a string, for a consumer that shows them together. |
| `status` | `Active` or `Deleted`. A deleted product stays in the listing on purpose. |
| `categories` | The categories of the product, with their names. |

## One product

### `GET /api/product/{id}`

The status 200 with the product, or the status 404 with
`"message": "Product not found."`.

### `POST /api/product`

**Body**

| Field | Type | Rule |
|---|---|---|
| `name` | string | Required, at most 100 characters. |
| `priceFrom` | number | Required, not negative, at most 13 digits with 4 decimals. |
| `priceTo` | number | Required, not negative, at least `priceFrom`. |
| `published` | string | Required, `Yes` or `No`. |
| `categories` | array of integers | Required, between 2 and 10 ids, all of them existing and all of them different. |

**Response:** the status 201 with the created product.

### `PUT /api/product/{id}`

The same body as the creation. The categories of the product are replaced by
the ones of the request. The status 200 with the changed product, or the
status 404.

### `DELETE /api/product/{id}`

The status 200. The row stays in the table with the date of the deletion, and
the product keeps appearing in the listing with the status `Deleted`.

## The rate limit

The api group of the framework carries the rate limiter `api`: 60 requests
per minute and per address. A request that passes the limit is answered with
the status 429.
