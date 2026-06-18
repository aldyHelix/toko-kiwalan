<?php

declare(strict_types=1);

namespace App\Modules\Branch\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a storefront branch selection at the boundary: the chosen branch
 * must exist and be active (conventions — Form Request for storefront input).
 */
final class SelectBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')->where('is_active', true),
            ],
        ];
    }
}
