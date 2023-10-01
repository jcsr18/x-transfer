<?php

namespace App\Http\Requests\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => 'required|exists:users,transfer_key',
            'to' => 'required|exists:users,transfer_key',
            'amount' => 'required|numeric',
        ];
    }
}
