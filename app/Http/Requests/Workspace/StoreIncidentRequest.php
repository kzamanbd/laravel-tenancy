<?php

declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Enums\ComponentStatus;
use App\Enums\IncidentImpact;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Incident::class);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', new Enum(IncidentStatus::class)],
            'impact' => ['required', new Enum(IncidentImpact::class)],
            'is_published' => ['boolean'],

            // The opening update is part of creating an incident: a page that
            // announces a problem without saying anything about it is worse
            // than one that stays quiet.
            'body' => ['required', 'string', 'max:5000'],

            'component_ids' => ['array'],
            'component_ids.*' => [Rule::exists('components', 'id')],
            'component_status' => ['required_with:component_ids', new Enum(ComponentStatus::class)],
        ];
    }
}
