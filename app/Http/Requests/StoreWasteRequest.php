<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWasteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'food_id' => ['required', 'exists:foods,id'],
            'quantity' => ['required', 'numeric', 'min:0.1'],
            'unit' => ['nullable', 'string', 'max:50'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'in:burned,expired,overproduction,damaged,spoiled,customer_return,other'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
