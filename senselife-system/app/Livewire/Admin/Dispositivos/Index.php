<?php

namespace App\Livewire\Admin\Dispositivos;

use App\Enums\DispositivoEstado;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Institucion\CentroMedico;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('admin.layouts.app')]
#[Title('Dispositivos')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $estado = '';

    #[Url(except: '')]
    public string $centro = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedCentro(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $base = Dispositivo::query()
            ->with(['hardwareModelo', 'centroMedico'])
            ->when($this->search !== '', function ($query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('numero_serie', 'like', $term)
                        ->orWhereHas('hardwareModelo', fn ($q) => $q->where('nombre', 'like', $term))
                        ->orWhereHas('centroMedico', fn ($q) => $q->where('nombre', 'like', $term));
                });
            })
            ->when($this->estado !== '', fn ($q) => $q->where('estado', $this->estado))
            ->when($this->centro !== '', fn ($q) => $q->where('centro_medico_id', $this->centro));

        $total = Dispositivo::query()->count();
        $enUso = Dispositivo::query()->where('estado', DispositivoEstado::Activo->value)->count();
        $sinAsignar = $total - $enUso;

        return view('livewire.admin.dispositivos.index', [
            'dispositivos' => (clone $base)->orderBy('numero_serie')->paginate(10),
            'centros'      => CentroMedico::query()->orderBy('nombre')->get(['id', 'nombre']),
            'totales'      => [
                'total'        => $total,
                'en_uso'       => $enUso,
                'sin_asignar'  => $sinAsignar,
            ],
        ]);
    }
}
