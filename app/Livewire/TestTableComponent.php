<?php

namespace App\Livewire;

use Livewire\Component;

class TestTableComponent extends Component
{
    public $message = 'Test Table Component Working!';

    public function render()
    {
        return view('livewire.test-table-component')
            ->layout('components.layouts.app');
    }
}
