<?php

namespace App\Livewire;

use App\Services\VaultService;
use Livewire\Component;
use Livewire\Attributes\On;

class TopbarContent extends Component
{
    public float $balance = 0;

    public function mount(VaultService $vaultService)
    {
        $this->balance = $vaultService->getVault()->balance;
    }

    #[On('vault-updated')]
    public function refreshBalance(VaultService $vaultService)
    {
        $this->balance = $vaultService->getVault()->balance;
    }

    public function render()
    {
        return view('livewire.topbar-content');
    }
}
