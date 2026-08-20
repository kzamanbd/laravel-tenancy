<?php

declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Enums\ComponentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('component'));
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
            'component_group_id' => ['nullable', Rule::exists('component_groups', 'id')],
        ];
    }
}
