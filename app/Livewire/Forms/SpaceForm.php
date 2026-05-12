<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class SpaceForm extends Form
{
    #[Validate(['required', 'min:2', 'max:100', 'unique:spaces,name'])]
    public string $name = '';

    public string $color = '#6366f1';

    public string $icon = 'folder';

    public function messages(): array
    {
        return [
            'name.required' => 'Nama space wajib diisi.',
            'name.min' => 'Nama space minimal 2 karakter.',
            'name.max' => 'Nama space maksimal 100 karakter.',
            'name.unique' => 'Nama space sudah digunakan.',
        ];
    }

    public function reset(mixed ...$properties): void
    {
        parent::reset(...$properties);
        $this->color = '#6366f1';
        $this->icon = 'folder';
    }
}
