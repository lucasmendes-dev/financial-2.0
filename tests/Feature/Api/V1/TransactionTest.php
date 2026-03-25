<?php

namespace Tests\Feature\Api\V1;

use App\Models\Asset;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_only_user_transactions()
    {
        $otherUser = User::factory()->create();
        $asset = Asset::factory()->create();

        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset->id,
        ]);

        Transaction::factory()->count(1)->create([
            'user_id' => $otherUser->id,
            'asset_id' => $asset->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'asset_id', 'asset_ticker', 'type', 'quantity', 
                        'price_per_asset', 'total', 'executed_at'
                    ]
                ],
                'meta' => ['total', 'per_page', 'current_page', 'last_page']
            ]);
    }

    public function test_can_create_transaction()
    {
        $asset = Asset::factory()->create();
        $data = [
            'ticker' => $asset->ticker,
            'type' => 'buy',
            'quantity' => 10.5,
            'price_per_asset' => 150.75,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/transactions', $data);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'data' => [
                    'asset_id' => $asset->id,
                    'type' => 'buy',
                    'quantity' => 10.5,
                    'price_per_asset' => 150.75,
                    'total' => 1582.875,
                ]
            ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'asset_id' => $asset->id,
            'type' => 'buy',
            'quantity' => 10.5,
        ]);
    }

    public function test_can_show_transaction()
    {
        $asset = Asset::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/transactions/{$transaction->id}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $transaction->id,
                    'asset_id' => $asset->id,
                    'type' => $transaction->type,
                    'quantity' => (float) $transaction->quantity,
                ]
            ]);
    }

    public function test_cannot_show_others_transaction()
    {
        $otherUser = User::factory()->create();
        $asset = Asset::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'asset_id' => $asset->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/transactions/{$transaction->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_can_update_transaction()
    {
        $asset = Asset::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset->id,
        ]);

        $updateData = [
            'quantity' => 20.0,
            'total' => 3000.0,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/transactions/{$transaction->id}", $updateData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'quantity' => 20.0,
                    'total' => 3000.0,
                ]
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'quantity' => 20.0,
            'total' => 3000.0,
        ]);
    }

    public function test_can_delete_transaction()
    {
        $asset = Asset::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $asset->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/transactions/{$transaction->id}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Deleted successfully']);

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_unauthenticated_user_cannot_access_transactions()
    {
        $response = $this->getJson('/api/v1/transactions');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_can_filter_transactions_by_type()
    {
        $asset = Asset::factory()->create();
        Transaction::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset->id, 'type' => 'buy']);
        Transaction::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset->id, 'type' => 'sell']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?type=buy');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'buy');
    }

    public function test_can_filter_transactions_by_quantity_range()
    {
        $asset = Asset::factory()->create();
        Transaction::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset->id, 'quantity' => 10]);
        Transaction::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset->id, 'quantity' => 50]);
        Transaction::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset->id, 'quantity' => 100]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?quantity_gt=20&quantity_lt=80');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.quantity', 50);
    }

    public function test_can_filter_transactions_by_price_range()
    {
        $asset = Asset::factory()->create();
        Transaction::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset->id, 'price_per_asset' => 100]);
        Transaction::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset->id, 'price_per_asset' => 200]);
        Transaction::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset->id, 'price_per_asset' => 300]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?price_gt=150&price_lt=250');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.price_per_asset', 200);
    }

    public function test_can_filter_transactions_by_executed_at_range()
    {
        $asset = Asset::factory()->create();
        Transaction::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset->id, 'executed_at' => '2023-01-01 10:00:00']);
        Transaction::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset->id, 'executed_at' => '2023-06-01 10:00:00']);
        Transaction::factory()->create(['user_id' => $this->user->id, 'asset_id' => $asset->id, 'executed_at' => '2023-12-01 10:00:00']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/transactions?executed_at_gt=2023-03-01&executed_at_lt=2023-09-01');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['executed_at' => '2023-06-01 10:00:00']);
    }
}
