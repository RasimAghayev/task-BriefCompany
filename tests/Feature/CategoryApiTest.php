<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/category';

    public function test_the_listing_answers_with_the_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson(self::ENDPOINT);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonCount(3, 'data');
    }

    public function test_the_listing_is_paged(): void
    {
        Category::factory()->count(20)->create();

        $response = $this->getJson(self::ENDPOINT.'?per_page=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.last_page', 4);
    }

    public function test_the_number_of_rows_of_a_page_is_bounded(): void
    {
        Category::factory()->count(3)->create();

        $this->getJson(self::ENDPOINT.'?per_page=1000')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 100);
    }

    public function test_a_category_is_created_with_a_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT, ['name' => 'Books']);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Books');

        $this->assertDatabaseHas('categories', ['name' => 'Books']);
    }

    public function test_the_creation_without_a_token_is_refused(): void
    {
        $response = $this->postJson(self::ENDPOINT, ['name' => 'Books']);

        $response->assertUnauthorized();
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_a_category_without_a_name_is_refused(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson(self::ENDPOINT, ['name' => '']);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validator');

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_a_parent_that_does_not_exist_is_refused(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(self::ENDPOINT, ['name' => 'Books', 'parent_id' => 999])
            ->assertStatus(422);
    }

    public function test_a_subcategory_is_stored_with_its_parent(): void
    {
        $parent = Category::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(self::ENDPOINT, ['name' => 'Novels', 'parent_id' => $parent->id])
            ->assertCreated()
            ->assertJsonPath('data.parent_id', $parent->id);
    }

    public function test_a_category_that_does_not_exist_answers_not_found(): void
    {
        $this->getJson(self::ENDPOINT.'/999')
            ->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Category not found.');
    }

    public function test_a_category_is_updated(): void
    {
        $category = Category::factory()->create(['name' => 'Books']);
        Sanctum::actingAs(User::factory()->create());

        $this->putJson(self::ENDPOINT.'/'.$category->id, ['name' => 'Music'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Music');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Music']);
    }

    public function test_a_category_is_deleted(): void
    {
        $category = Category::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson(self::ENDPOINT.'/'.$category->id)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_a_category_that_does_not_exist_is_not_deleted(): void
    {
        Sanctum::actingAs(User::factory()->create());

        // Before, the lookup of the category was missing, so a request for
        // a category that does not exist ended in an error of the framework.
        $this->deleteJson(self::ENDPOINT.'/999')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Category not found.');
    }

    public function test_a_category_that_belongs_to_a_product_is_not_deleted(): void
    {
        $category = Category::factory()->create();
        $other = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach([$category->id, $other->id]);

        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson(self::ENDPOINT.'/'.$category->id)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Category used other product.');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
