<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'nombre_usuario' => 'required|string|max:20',
        'id_rol' => 'required|exists:rol,id_rol',
        'contraseña_administrador' => 'nullable|min:6',
        'pin_usuario' => 'nullable|digits:4',
    ];
    }
}
