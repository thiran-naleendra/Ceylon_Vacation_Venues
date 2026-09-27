<?php

namespace App\Http\Requests\Admin\AuditLogs;

use App\Models\AuditLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAuditLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', AuditLog::class) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'string', 'max:100', Rule::exists('audit_logs', 'action')],
            'actor' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'subject_type' => ['nullable', 'string', 'max:100', Rule::exists('audit_logs', 'subject_type')],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ];
    }
}
