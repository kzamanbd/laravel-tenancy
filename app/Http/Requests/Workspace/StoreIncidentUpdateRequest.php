<?php

declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Enums\IncidentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreIncidentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('comment', $this->route('incident'));
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(IncidentStatus::class)],
            'body' => ['required', 'string', 'max:5000'],

            // Unpublished updates are the review queue an AI draft lands in.
            'publish' => ['boolean'],
        ];
    }
}
