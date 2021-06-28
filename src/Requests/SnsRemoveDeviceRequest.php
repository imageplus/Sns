<?php

namespace Imageplus\Sns\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Imageplus\Sns\Rules\IsValidPlatform;

class SnsRemoveDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'device_token' => 'required',
            'platform'     => [
                'required',
                new IsValidPlatform()
            ]
        ];
    }
}
