<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        $user = $this->user(); 
        if(!$user)
            return false;
        return $user -> rol ->nombre === 'Administrador'; 
    }

    public function rules(): array
    {
        $user = $this->route('Usuario'); 
        $operacion = $this->isMethod('PUT') || $this ->isMethod('PATCH'); 
        return [
            'nombre_usuario' => [
                 $isUpdate ? 'sometimes' : 'required',
                'string',
                'min:4',
                'max:50',
                'alpha_dash', 
                Rule::unique('usuario', 'username')->ignore($userId),
            ],
            'contraseña_administrador' => [
                $this -> isCreatingAdmin() ? 'required' : 'nullable', 
                'string', 
                'min= 3', 
                'max=255',  
            ], 
             'pin_usuario' => [
                $this->isCreatingNonAdmin() ? 'required' : 'nullable',
                'string',
                'digits:4', 
                'regex:/^[0-9]{4}$/',
            ],
            'estado' => [
                'sometimes',
                'boolean',
            ], 
             'id_rol' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'exists:rol,id', 
            ],

        ];
    }

     public function messages(): array
    {
        return [

            'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
            'nombre_usuario.min' => 'El nombre de usuario debe tener al menos 4 caracteres.',
            'nombre_usuario.unique' => 'Este nombre de usuario ya está en uso.',
            'nombre_usuario.alpha_dash' => 'El nombre de usuario solo puede contener letras, números, guiones y guiones bajos.',

            'id_rol.required' => 'Debe seleccionar un rol.',
            'id_rol.exists' => 'El rol seleccionado no existe.',

            'contraseña_administrador.required' => 'La contraseña es obligatoria para administradores.',
            'contraseña_administrador.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'contraseña_administrador.regex' => 'La contraseña debe contener mayúsculas, minúsculas y números.',

            'pin_usuario.required' => 'El PIN es obligatorio para supervisores y empleados.',
            'pin_usuario.digits' => 'El PIN debe tener exactamente 4 dígitos.',
            'pin_usuario.regex' => 'El PIN solo puede contener números.',

            'estado.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_usuario' => 'nombre de usuario',
            'id_rol' => 'rol',
            'contraseña_administrador' => 'contraseña',
            'pin_usuario' => 'PIN',
            'estado' => 'estado',
        ];
    }

    protected function creandoAdministrador(): bool
    {
        return !$this->route('usuario') && $this->input('id_rol') == 1;
    }

    protected function creandoOtroRol(): bool
    {
        return !$this->route('usuario') && $this->input('id_rol') != 1;
    }


    protected function prepararValidacion(): void
    {
        if ($this->has('nombre_usuario')) {
            $this->merge([
                'nombre_usuario' => strtolower(trim($this->username))
            ]);
        }
    }

    protected function errorValidacion(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}




