<?php

declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Enums\ComponentStatus;
use App\Models\Component;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Component::class);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', new Enum(ComponentStatus::class)],
            'is_public' => ['boolean'],
            'show_uptime' => ['boolean'],
            // Row-Level Security keeps this lookup inside the current tenant,
            // so a foreign group id simply fails to exist.
            'component_group_id' => ['nullable', Rule::exists('component_groups', 'id')],
        ];
    }
}
