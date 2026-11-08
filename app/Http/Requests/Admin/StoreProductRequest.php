<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasVerifiedEmail() ?? false;
    }

    /**
     * Prepare normalized input before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->string('slug')->toString() ?: $this->string('name')->toString()),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')],
            'description' => ['nullable', 'string', 'max:5000'],
            'materials' => ['nullable', 'string', 'max:5000'],
            'care_instructions' => ['nullable', 'string', 'max:5000'],
            'base_price' => ['required', 'integer', 'min:0', 'max:999999999'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'variants' => ['required', 'array', 'min:1', 'max:50'],
            'variants.*.id' => ['prohibited'],
            'variants.*.sku' => ['required', 'string', 'max:255', 'distinct:strict'],
            'variants.*.size' => ['required', 'string', 'max:24'],
            'variants.*.color' => ['required', 'string', 'max:80'],
            'variants.*.color_hex' => ['nullable', 'string', 'max:16', 'regex:/^#?[0-9A-Fa-f]{3,8}$/'],
            'variants.*.price' => ['required', 'integer', 'min:0', 'max:999999999'],
            'variants.*.stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'variants.*.is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $seenOptions = [];

                foreach ($this->input('variants', []) as $index => $variant) {
                    $sku = $variant['sku'] ?? null;
                    $optionKey = Str::lower(($variant['size'] ?? '').'|'.($variant['color'] ?? ''));

                    if ($sku && ProductVariant::query()->where('sku', $sku)->exists()) {
                        $validator->errors()->add("variants.{$index}.sku", 'SKU sudah digunakan.');
                    }

                    if (isset($seenOptions[$optionKey])) {
                        $validator->errors()->add("variants.{$index}.color", 'Kombinasi size dan color tidak boleh duplikat.');
                    }

                    $seenOptions[$optionKey] = true;
                }
            },
        ];
    }
}
