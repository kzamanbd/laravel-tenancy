<?php

declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Models\Maintenance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Maintenance::class);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'scheduled_start_at' => ['required', 'date'],
            'scheduled_end_at' => ['required', 'date', 'after:scheduled_start_at'],
            'is_published' => ['boolean'],
            'auto_transition' => ['boolean'],
            'notify_subscribers' => ['boolean'],
            'component_ids' => ['array'],
            'component_ids.*' => [Rule::exists('components', 'id')],
        ];
    }
}
