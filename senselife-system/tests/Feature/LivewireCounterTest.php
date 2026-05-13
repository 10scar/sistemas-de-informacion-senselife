<?php

namespace Tests\Feature;

use App\Livewire\Counter;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireCounterTest extends TestCase
{
    public function test_livewire_demo_page_renders(): void
    {
        $this->get('/livewire-demo')
            ->assertOk()
            ->assertSee('Counter');
    }

    public function test_counter_increments(): void
    {
        Livewire::test(Counter::class)
            ->assertSet('count', 0)
            ->call('increment')
            ->assertSet('count', 1);
    }
}
