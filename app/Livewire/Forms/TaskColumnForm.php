<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class TaskColumnForm extends Form
{
    #[Validate(['required', 'string', 'max:100'])]
    public string $name = '';

    public string $color = '#6b7280';

    public function reset(mixed ...$properties): void
    {
        parent::reset(...$properties);
        $this->color = '#6b7280';
    }
}
