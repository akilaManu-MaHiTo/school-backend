<?php

namespace App\Http\Requests\StudentServiceCharges;

use Illuminate\Foundation\Http\FormRequest;

class StudentServiceChargesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId'         => ['required', 'integer', 'exists:users,id'],
            'chargesCategoryId' => ['required', 'integer', 'exists:com_payment_categories,id'],
            'amount'            => ['required', 'numeric', 'min:0'],
            'dateCharged'       => ['required', 'string', 'max:255'],
            'yearForCharge'     => ['required', 'integer'],
            'remarks'           => ['nullable', 'string', 'max:1000'],
        ];
    }
}
