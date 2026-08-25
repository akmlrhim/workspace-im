<?php

namespace App\Livewire\Concerns;

use Flux\Flux;
use Illuminate\Support\Facades\Validator;

trait ValidatesWithToast
{
    /**
     * @param  array<string, string>  $rules  keyed by component property path
     * @param  array<string, string>  $messages
     */
    private function validateWithToast(array $rules, array $messages = []): bool
    {
        $data = [];

        foreach (array_keys($rules) as $field) {
            $data[$field] = data_get($this, $field);
        }

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            Flux::toast($validator->errors()->first(), variant: 'danger');

            return false;
        }

        return true;
    }
}
