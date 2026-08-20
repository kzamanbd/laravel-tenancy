<?php

declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Enums\IncidentImpact;
use App\Enums\IncidentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('incident'));
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'impact' => ['required', new Enum(IncidentImpact::class)],
            'status' => ['required', new Enum(IncidentStatus::class)],
            'is_published' => ['boolean'],
        ];
    }
}
