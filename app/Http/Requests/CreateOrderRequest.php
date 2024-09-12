<?php

namespace App\Http\Requests;

use App\Enum\CurrencyEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'give' => ['required', 'array'],
            'give.amount' => ['required', 'string'],
            'give.currency' => ['required', 'string', Rule::in(CurrencyEnum::getCodes())],
            'receive' => ['required', 'array'],
            'receive.amount' => ['required', 'string'],
            'receive.currency' => ['required', 'string', Rule::in(CurrencyEnum::getCodes())],
            'account_give_id' => ['required', 'string', 'uuid'],
            'account_receive_id' => ['required', 'string', 'uuid'],
        ];
    }
}
