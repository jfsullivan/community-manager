<?php

namespace jfsullivan\CommunityManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinCommunityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'state.join_id' => 'required|integer',
            'state.password' => 'required|string',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'state.join_id' => 'Community ID',
            'state.password' => 'Community Password',
        ];
    }
}
