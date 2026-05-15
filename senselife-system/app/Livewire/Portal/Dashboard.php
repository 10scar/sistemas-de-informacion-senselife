<?php

namespace App\Livewire\Portal;

use App\Support\RolNombre;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Portal clínico')]
class Dashboard extends Component
{
    public function render()
    {
        $nombreRol = auth()->user()?->rol?->nombre;

        $section = match ($nombreRol) {
            RolNombre::MEDICO => __('Médico'),
            RolNombre::OPERADOR => __('Centro'),
            default => __('Portal'),
        };

        return view('livewire.portal.dashboard')
            ->layout('components.layouts.portal', ['section' => $section]);
    }
}
