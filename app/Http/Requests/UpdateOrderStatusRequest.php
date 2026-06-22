<?php

namespace App\Http\Requests;

use App\Domain\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:' . implode(',', array_column(OrderStatus::cases(), 'value')),
        ];
    }
}