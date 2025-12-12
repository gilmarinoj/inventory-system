<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'], // 2MB max
            'barcode' => ['required', 'string', 'max:50', 'unique:products,barcode'],
            'price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'price_bsd'   => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'quantity' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio!',
            'barcode.required' => 'El código de barras del producto es obligatorio!',
            'barcode.unique' => 'El código de barras del producto debe ser único!',
            'price.required' => 'El precio del producto es obligatorio!',
            'price.decimal' => __('product.validation.price_decimal'),
            'quantity.required' => __('product.validation.quantity_required'),
            'quantity.min' => __('product.validation.quantity_min'),
            'image.max' => __('product.validation.image_max'),
        ];
    }
}
