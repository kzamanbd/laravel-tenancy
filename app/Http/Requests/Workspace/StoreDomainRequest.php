<?php

declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Models\Domain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Domain::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'domain' => strtolower(trim((string) $this->input('domain'))),
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'domain' => [
                'required',
                'string',
                'max:253',
                // Hostname shape only: no scheme, no path, no port, no
                // wildcard. A wildcard here would ask Caddy for a certificate
                // covering names nobody proved they own.
                'regex:/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))+$/',
                Rule::unique('domains', 'domain'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'domain.regex' => 'Enter a hostname such as status.example.com, without https:// or a trailing path.',
            'domain.unique' => 'That hostname is already connected to a status page.',
        ];
    }
}
