<?php

use Dystcz\LunarApi\Domain\ProductVariants\Models\ProductVariant;
use Dystcz\LunarApiProductNotification\Domain\ProductNotifications\Factories\ProductNotificationFactory;
use Dystcz\LunarApiProductNotification\Domain\ProductNotifications\Models\ProductNotification;
use Dystcz\LunarApiProductNotification\Domain\ProductNotifications\Notifications\ProductRestockedNotification;
use Dystcz\LunarApiProductNotification\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(TestCase::class, RefreshDatabase::class);

it('notifies user on product stock change', function () {
    /** @var TestCase $this */
    $notification = ProductNotification::factory()
        ->for(
            ProductVariant::factory()->state(['stock' => 0]),
            'purchasable'
        )
        ->create();

    $productVariant = $notification->purchasable;

    Notification::fake();

    $productVariant->update(['stock' => 1]);

    Notification::assertSentTo(
        $notification,
        ProductRestockedNotification::class,
        function ($notification, $channels) use ($productVariant) {
            return $notification->productVariant->is($productVariant);
        }
    );
});

it('notifies the user only once', function () {
    /** @var TestCase $this */
    $notification = ProductNotificationFactory::new()
        ->for(
            ProductVariant::factory()->state(['stock' => 0]),
            'purchasable'
        )
        ->create(['sent_at' => now()]);

    $productVariant = $notification->purchasable;

    Notification::fake();

    $productVariant->update(['stock' => 1]);

    Notification::assertNothingSent();
});
