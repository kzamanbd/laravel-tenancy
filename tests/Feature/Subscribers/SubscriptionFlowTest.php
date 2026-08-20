<?php

declare(strict_types=1);

use App\Enums\Plan;
use App\Enums\SubscriberChannel;
use App\Mail\ConfirmSubscription;
use App\Models\Organization;
use App\Models\Subscriber;
use App\Models\Tenant;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Public subscription flow
|--------------------------------------------------------------------------
|
| Every tenant sends through the same reputation. A page that could harvest
| addresses and mail them unasked would degrade deliverability for everyone,
| so nothing is ever sent to an address that has not confirmed.
|
*/

beforeEach(function () {
    Mail::fake();

    $this->page = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->onPlan(Plan::Growth)->create())
        ->create(['name' => 'Acme Platform']));
});

it('creates an unconfirmed subscriber and sends one confirmation', function () {
    $this->post("/status/{$this->page->id}/subscribe", ['email' => 'reader@example.com'])
        ->assertOk()
        ->assertSee('Check your email');

    $subscriber = asSuperAdmin(fn () => Subscriber::query()->firstOrFail());

    expect($subscriber->endpoint)->toBe('reader@example.com')
        ->and($subscriber->channel)->toBe(SubscriberChannel::Email)
        ->and($subscriber->confirmed_at)->toBeNull()
        ->and($subscriber->isDeliverable())->toBeFalse();

    Mail::assertQueued(ConfirmSubscription::class);
});

it('confirms a subscriber through the emailed link', function () {
    $this->post("/status/{$this->page->id}/subscribe", ['email' => 'reader@example.com']);

    $subscriber = asSuperAdmin(fn () => Subscriber::query()->firstOrFail());

    $this->get("/subscriptions/confirm/{$subscriber->confirmation_token}")
        ->assertOk()
        ->assertSee('Subscription confirmed');

    $fresh = asSuperAdmin(fn () => $subscriber->fresh());

    expect($fresh->isDeliverable())->toBeTrue()
        // The token is single-use: it stops being a way to re-confirm an
        // address somebody else unsubscribed.
        ->and($fresh->confirmation_token)->toBeNull();
});

it('rejects an unknown confirmation token', function () {
    $this->get('/subscriptions/confirm/not-a-real-token')->assertNotFound();
});

it('does not re-send to an address that already confirmed', function () {
    $this->post("/status/{$this->page->id}/subscribe", ['email' => 'reader@example.com']);
    $subscriber = asSuperAdmin(fn () => Subscriber::query()->firstOrFail());
    $this->get("/subscriptions/confirm/{$subscriber->confirmation_token}");

    Mail::fake();

    // Otherwise the form is a way to mail a stranger repeatedly by typing
    // their address into it.
    $this->post("/status/{$this->page->id}/subscribe", ['email' => 'reader@example.com'])
        ->assertOk();

    Mail::assertNothingQueued();
    expect(asSuperAdmin(fn () => Subscriber::query()->count()))->toBe(1);
});

it('answers identically whether or not the address is already subscribed', function () {
    $first = $this->post("/status/{$this->page->id}/subscribe", ['email' => 'reader@example.com']);
    $second = $this->post("/status/{$this->page->id}/subscribe", ['email' => 'reader@example.com']);

    // Differing responses would turn the form into a way to test whether an
    // address is subscribed to a given page.
    expect($second->status())->toBe($first->status());
});

it('unsubscribes in one click with no confirmation step', function () {
    $subscriber = asSuperAdmin(fn () => Subscriber::factory()->forTenant($this->page)->create());

    $this->get("/subscriptions/unsubscribe/{$subscriber->unsubscribe_token}")
        ->assertOk()
        ->assertSee('Unsubscribed');

    expect(asSuperAdmin(fn () => $subscriber->fresh())->isDeliverable())->toBeFalse();
});

it('lets someone who unsubscribed sign up again', function () {
    $subscriber = asSuperAdmin(fn () => Subscriber::factory()->forTenant($this->page)->unsubscribed()
        ->create(['endpoint' => 'reader@example.com']));

    $this->post("/status/{$this->page->id}/subscribe", ['email' => 'reader@example.com']);

    $fresh = asSuperAdmin(fn () => $subscriber->fresh());

    expect($fresh->unsubscribed_at)->toBeNull();
    Mail::assertQueued(ConfirmSubscription::class);
});

it('rejects an invalid email address', function () {
    // No session exists to flash errors into: the form was submitted from a
    // static file that may not even be on this domain.
    $this->post("/status/{$this->page->id}/subscribe", ['email' => 'not-an-email'])
        ->assertOk()
        ->assertSee('did not look right');

    Mail::assertNothingQueued();
});

it('404s for a page that does not exist', function () {
    $this->post('/status/999999/subscribe', ['email' => 'reader@example.com'])->assertNotFound();
});

it('refuses sign-ups once the plan limit is reached', function () {
    $page = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->onPlan(Plan::Free)->create())
        ->create());

    // Free allows 50; accepting the 51st and silently never mailing it would
    // be worse than refusing.
    asSuperAdmin(fn () => Subscriber::factory()->count(50)->forTenant($page)->create());

    $this->post("/status/{$page->id}/subscribe", ['email' => 'one-too-many@example.com']);

    expect(asSuperAdmin(fn () => Subscriber::query()->where('tenant_id', $page->id)->count()))->toBe(50);
    Mail::assertNothingQueued();
});

it('keeps subscribers on the page they signed up to', function () {
    $other = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->onPlan(Plan::Growth)->create())
        ->create());

    $this->post("/status/{$this->page->id}/subscribe", ['email' => 'reader@example.com']);
    $this->post("/status/{$other->id}/subscribe", ['email' => 'reader@example.com']);

    $mine = asSuperAdmin(fn () => Subscriber::query()->where('tenant_id', $this->page->id)->count());
    $theirs = asSuperAdmin(fn () => Subscriber::query()->where('tenant_id', $other->id)->count());

    expect($mine)->toBe(1)->and($theirs)->toBe(1);
});

it('accepts the form without a CSRF token', function () {
    // The published page is a static file on a CDN. It cannot carry a fresh
    // token, and a stale one would reject real readers -- so these routes run
    // without a session at all.
    $this->post("/status/{$this->page->id}/subscribe", ['email' => 'reader@example.com'])
        ->assertOk()
        ->assertSee('Check your email');

    expect(asSuperAdmin(fn () => Subscriber::query()->count()))->toBe(1);
});
