<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            'clock_in'  => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'breaks' => ['array'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'   => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $in  = $this->input('clock_in');
            $out = $this->input('clock_out');

            if ($in && $out && $in >= $out) {
                $v->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $breaks = $this->input('breaks', []);
            foreach ($breaks as $i => $b) {
                $s = $b['start'] ?? null;
                $e = $b['end'] ?? null;

                if ($s && $in && $s < $in) {
                    $v->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                }
                if ($s && $out && $s > $out) {
                    $v->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                }
                if ($e && $out && $e > $out) {
                    $v->errors()->add("breaks.$i.end", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }
}