<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name'     => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'personal_email' => ['required', 'email'],
            'role' => ['required', 'string', 'in:student,faculty,computer_lab_supervisor,venue_hall_supervisor,library_supervisor,sports_equipment_supervisor']

        ];
    }
}
