<?php

declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Models\Component;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePageSettingsRequest extends FormRequest
{
    /**
     * Presentation is a write to the public page, so it takes the same
     * authority as changing what the page reports.
     */
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
            'headline' => ['nullable', 'string', 'max:255'],
            'support_url' => ['nullable', 'url', 'max:255'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'timezone' => ['required', 'timezone'],

            // Sanitised again at publish time; this only keeps obvious rubbish
            // out of the database.
            'custom_css' => ['nullable', 'string', 'max:20000'],

            'show_powered_by' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'primary_color.regex' => 'The primary colour must be a hex value such as #4f46e5.',
        ];
    }
}
