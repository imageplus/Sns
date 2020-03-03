<?php

namespace Imageplus\Sns\src\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SnsAddDeviceRequest extends FormRequest
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
            'platform' => 'required'
        ];
    }
}
