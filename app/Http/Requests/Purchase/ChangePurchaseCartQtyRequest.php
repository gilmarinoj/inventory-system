<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class ChangePurchaseCartQtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => __('El producto es necesario'),
            'product_id.exists' => __('Producto no encontrado'),
            'quantity.required' => __('La cantidad es necesaria'),
            'quantity.integer' => __('La cantidad debe ser un numero'),
            'quantity.min' => __('La cantidad debe ser al menos 1'),
        ];
    }
}
