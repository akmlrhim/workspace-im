<?php

namespace App\Livewire\Project\Concerns;

use Flux\Flux;
use Illuminate\Support\Facades\Validator;

/**
 * Inline validation that surfaces the first failure as a toast instead of
 * an error bag, for the components that edit in place rather than via forms.
 */
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
