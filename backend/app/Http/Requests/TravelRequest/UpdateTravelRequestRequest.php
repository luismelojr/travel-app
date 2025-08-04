<?php

namespace App\Http\Requests\TravelRequest;

use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTravelRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requester_name' => ['sometimes', 'string', 'max:255'],
            'destination' => ['sometimes', 'string', 'max:255'],
            'departure_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'return_date' => ['sometimes', 'date', 'after:departure_date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'requester_name.string' => 'O nome do solicitante deve ser um texto.',
            'requester_name.max' => 'O nome do solicitante não pode ter mais de 255 caracteres.',
            
            'destination.string' => 'O destino deve ser um texto.',
            'destination.max' => 'O destino não pode ter mais de 255 caracteres.',
            
            'departure_date.date' => 'A data de partida deve ser uma data válida.',
            'departure_date.after_or_equal' => 'A data de partida não pode ser anterior a hoje.',
            
            'return_date.date' => 'A data de retorno deve ser uma data válida.',
            'return_date.after' => 'A data de retorno deve ser posterior à data de partida.',
            
            'notes.string' => 'As observações devem ser um texto.',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::validationError($validator->errors()->toArray())
        );
    }
}
