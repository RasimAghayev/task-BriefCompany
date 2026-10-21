<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/product';

    public function test_the_listing_answers_with_the_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson(self::ENDPOINT);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonCount(3, 'data');
    }

    public function test_the_listing_holds_the_name_of_the_product(): void
    {
        Product::factory()->create(['name' => 'A book of tests']);

        // The name was missing from the response before, so a consumer had
        // no way to tell two products apart.
        $this->getJson(self::ENDPOINT)
            ->assertOk()
            ->assertJsonPath('data.0.name', 'A book of tests');
    }

    public function test_the_listing_holds_the_categories_of_the_product(): void
    {
        $product = Product::factory()->create();
        $first = Category::factory()->create(['name' => 'Books']);
        $second = Category::factory()->create(['name' => 'Novels']);
        $product->categories()->attach([$first->id, $second->id]);

        $this->getJson(self::ENDPOINT)
            ->assertOk()
            ->assertJsonPath('data.0.categories.0.category_name', 'Books')
            ->assertJsonPath('data.0.categories.1.category_name', 'Novels');
    }

    public function test_a_product_is_created_with_a_token(): void
    {
        $categories = Category::factory()->count(2)->create();
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT, [
                'name' => 'A book of tests',
                'priceFrom' => 10.5,
                'priceTo' => 20.25,
                'published' => 'Yes',
                'categories' => $categories->pluck('id')->all(),
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'A book of tests')
            ->assertJsonPath('data.priceFrom', 10.5)
            ->assertJsonCount(2, 'data.categories');

        $this->assertDatabaseHas('products', ['name' => 'A book of tests']);
        $this->assertDatabaseCount('category_product', 2);
    }

    public function test_the_creation_without_a_token_is_refused(): void
    {
        $categories = Category::factory()->count(2)->create();

        $response = $this->postJson(self::ENDPOINT, [
            'name' => 'A book of tests',
            'priceFrom' => 10.5,
            'priceTo' => 20.25,
            'published' => 'Yes',
            'categories' => $categories->pluck('id')->all(),
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseCount('products', 0);
    }

    public function test_a_product_with_one_category_is_refused(): void
    {
        $category = Category::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(self::ENDPOINT, [
            'name' => 'A book of tests',
            'priceFrom' => 10.5,
            'priceTo' => 20.25,
            'published' => 'Yes',
            'categories' => [$category->id],
        ])->assertStatus(422);
    }

    public function test_a_product_with_a_category_that_does_not_exist_is_refused(): void
    {
        $category = Category::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson(self::ENDPOINT, [
            'name' => 'A book of tests',
            'priceFrom' => 10.5,
            'priceTo' => 20.25,
            'published' => 'Yes',
            'categories' => [$category->id, 999],
        ]);

        // Before, the categories were not checked, so the product was
        // written with a relation to a category that does not exist.
        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_a_product_with_an_upper_price_below_the_lower_price_is_refused(): void
    {
        $categories = Category::factory()->count(2)->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(self::ENDPOINT, [
            'name' => 'A book of tests',
            'priceFrom' => 20,
            'priceTo' => 10,
            'published' => 'Yes',
            'categories' => $categories->pluck('id')->all(),
        ])->assertStatus(422);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_a_price_that_is_not_a_number_is_refused(): void
    {
        $categories = Category::factory()->count(2)->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(self::ENDPOINT, [
            'name' => 'A book of tests',
            'priceFrom' => 'free',
            'priceTo' => 10,
            'published' => 'Yes',
            'categories' => $categories->pluck('id')->all(),
        ])->assertStatus(422);
    }

    public function test_a_product_that_does_not_exist_answers_not_found(): void
    {
        $this->getJson(self::ENDPOINT.'/999')
            ->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Product not found.');
    }

    public function test_the_update_changes_the_categories_of_the_product(): void
    {
        $product = Product::factory()->create();
        $first = Category::factory()->create();
        $second = Category::factory()->create();
        $product->categories()->attach($first->id);
        Sanctum::actingAs(User::factory()->create());

        $this->putJson(self::ENDPOINT.'/'.$product->id, [
            'name' => 'A book of tests',
            'priceFrom' => 10,
            'priceTo' => 20,
            'published' => 'No',
            'categories' => [$first->id, $second->id],
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data.categories');

        // The categories were not touched by the update before, so the
        // product kept the category it was created with.
        $this->assertDatabaseCount('category_product', 2);
    }

    public function test_a_product_is_deleted_and_keeps_its_row(): void
    {
        $product = Product::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson(self::ENDPOINT.'/'.$product->id)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
