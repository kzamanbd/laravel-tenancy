<?php

declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Enums\SubscriberChannel;
use App\Models\Subscriber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Subscriber::class);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            // Email is deliberately absent: an address added from the admin
            // would skip the confirmation step, which is the one thing keeping
            // the shared sending reputation intact.
            'channel' => ['required', Rule::in([
                SubscriberChannel::Slack->value,
                SubscriberChannel::Webhook->value,
                SubscriberChannel::Discord->value,
                SubscriberChannel::Teams->value,
            ])],
            'endpoint' => [
                'required',
                'url:https',
                'max:255',
                Rule::unique('subscribers', 'endpoint')->where('tenant_id', tenant()?->getTenantKey()),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'channel.in' => 'Email subscribers must sign up from the status page so they can confirm.',
            'endpoint.url' => 'Enter the full https:// webhook URL.',
        ];
    }
}
