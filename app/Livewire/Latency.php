<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

/**
 * Class Latency.
 *
 * This class is the latency component.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Latency extends Component
{
    public int $ping = -1;

    public function getPing()
    {
        $this->ping = now()->timestamp;
    }

    public function render()
    {
        return view('livewire.latency');
    }
}
