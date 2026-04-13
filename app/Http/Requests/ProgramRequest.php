<?php

namespace App\Http\Requests;

use App\Models\Program;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => $this->requiredRules(['string', 'max:150']),
            'course_type' => $this->optionalRules(['nullable', 'string', 'max:100']),
            'day' => $this->requiredRules([Rule::in(Program::DAYS)]),
            'start_time' => $this->requiredRules(['date_format:H:i']),
            'duration' => $this->requiredRules(['integer', 'min:15', 'max:240']),
            'coach' => $this->requiredRules(['string', 'max:120']),
            'room' => $this->requiredRules(['string', 'max:120']),
            'screen_id' => $this->optionalRules(['nullable', 'integer', Rule::exists('screens', 'id')]),
            'display_order' => $this->requiredRules(['integer', 'min:1']),
            'is_active' => $this->requiredRules(['boolean']),
        ];
    }

    public function messages(): array
    {
        return [
            'day.in' => 'Le jour selectionne est invalide.',
            'start_time.date_format' => 'L heure de debut doit etre au format HH:MM.',
            'duration.min' => 'La duree minimale est de 15 minutes.',
            'duration.max' => 'La duree maximale est de 240 minutes.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'screen_id' => $this->normalizeNullableInteger('screen_id'),
            'duration' => $this->normalizeNullableInteger('duration'),
            'display_order' => $this->normalizeNullableInteger('display_order'),
            'is_active' => $this->normalizeBoolean('is_active'),
            'course_type' => $this->normalizeNullableString('course_type'),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Program|null $program */
            $program = $this->route('program');

            $payload = array_merge(
                $program?->only([
                    'title',
                    'course_type',
                    'day',
                    'start_time',
                    'duration',
                    'coach',
                    'room',
                    'screen_id',
                    'display_order',
                    'is_active',
                ]) ?? [],
                $validator->safe()->toArray(),
            );

            if (Program::hasConflict($payload, $program?->id)) {
                $validator->errors()->add('room', 'Conflit detecte : cette salle est deja utilisee sur ce creneau.');
            }
        });
    }

    private function requiredRules(array $rules): array
    {
        if ($this->isMethod('post')) {
            return ['required', ...$rules];
        }

        return ['sometimes', 'required', ...$rules];
    }

    private function optionalRules(array $rules): array
    {
        if ($this->isMethod('post')) {
            return $rules;
        }

        return ['sometimes', ...$rules];
    }

    private function normalizeNullableInteger(string $key): mixed
    {
        if (! $this->exists($key)) {
            return $this->input($key);
        }

        $value = $this->input($key);

        if ($value === '' || $value === null) {
            return null;
        }

        return is_numeric($value) ? (int) $value : $value;
    }

    private function normalizeNullableString(string $key): ?string
    {
        if (! $this->exists($key)) {
            return $this->input($key);
        }

        $value = trim((string) $this->input($key));

        return $value === '' ? null : $value;
    }

    private function normalizeBoolean(string $key): mixed
    {
        if (! $this->exists($key)) {
            return $this->input($key);
        }

        $normalized = filter_var($this->input($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $normalized ?? $this->input($key);
    }
}
