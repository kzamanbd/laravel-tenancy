<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workspace;

use App\Enums\SubscriberChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreSubscriberRequest;
use App\Models\Subscriber;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SubscriberController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Subscriber::class);

        /** @var Tenant $tenant */
        $tenant = tenant();
        $limit = $tenant->organization?->plan->subscriberLimit();

        $confirmed = Subscriber::query()
            ->whereNotNull('confirmed_at')
            ->whereNull('unsubscribed_at')
            ->count();

        return Inertia::render('workspace/subscribers', [
            'subscribers' => Subscriber::query()
                ->orderByDesc('id')
                ->limit(200)
                ->get()
                ->map(fn (Subscriber $subscriber): array => [
                    'id' => $subscriber->id,
                    'channel' => $subscriber->channel->value,
                    'channelLabel' => $subscriber->channel->label(),
                    'endpoint' => $subscriber->endpoint,
                    'isDeliverable' => $subscriber->isDeliverable(),
                    'confirmedAt' => $subscriber->confirmed_at?->toIso8601String(),
                    'unsubscribedAt' => $subscriber->unsubscribed_at?->toIso8601String(),
                    'bounceCount' => $subscriber->bounce_count,
                    'lastNotifiedAt' => $subscriber->last_notified_at?->toIso8601String(),
                ]),
            'stats' => [
                'confirmed' => $confirmed,
                'pending' => Subscriber::query()->whereNull('confirmed_at')->whereNull('unsubscribed_at')->count(),
                'unsubscribed' => Subscriber::query()->whereNotNull('unsubscribed_at')->count(),
                'limit' => $limit,
                'atLimit' => $limit !== null && $confirmed >= $limit,
            ],
            'channels' => array_map(
                fn (SubscriberChannel $channel): array => [
                    'value' => $channel->value,
                    'label' => $channel->label(),
                    'metered' => $channel->isMetered(),
                ],
                SubscriberChannel::cases(),
            ),
            'can' => ['manage' => Gate::allows('create', Subscriber::class)],
        ]);
    }

    /**
     * Adding an integration endpoint by hand: a Slack or webhook URL the
     * operator owns, rather than an address belonging to somebody else. Email
     * is excluded because that path must go through double opt-in.
     */
    public function store(StoreSubscriberRequest $request): RedirectResponse
    {
        $channel = SubscriberChannel::from($request->validated('channel'));

        Subscriber::create([
            'channel' => $channel,
            'endpoint' => $request->validated('endpoint'),
        ])->forceFill(['confirmed_at' => now()])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Integration added.')]);

        return back();
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        Gate::authorize('delete', $subscriber);

        $subscriber->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Subscriber removed.')]);

        return back();
    }
}
