<?php

namespace App\Livewire\Portal;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('portal.layouts.app')]
#[Title('Portal clínico')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.portal.dashboard');
    }
}
