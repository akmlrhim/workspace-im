<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class TaskListForm extends Form
{
    #[Validate(['required', 'min:2', 'max:100'])]
    public string $name = '';

    #[Validate(['required', 'exists:spaces,id'])]
    public ?int $spaceId = null;

    public function messages(): array
    {
        return [
            'name.required' => 'Nama list wajib diisi.',
            'name.min' => 'Nama list minimal 2 karakter.',
            'spaceId.required' => 'Pilih space terlebih dahulu.',
            'spaceId.exists' => 'Space tidak valid.',
        ];
    }
}
