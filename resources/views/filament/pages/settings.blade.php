<x-filament-panels::page>
    {{-- <form wire:submit="create">
        {{ $this->form }}

        <button type="submit">
            Submit
        </button>
    </form> --}}
    <x-filament-panels::form id="form" :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
        wire:submit="save">
        {{ $this->form }}

        {{-- <x-filament-panels::form.actions :actions="[]" :full-width="true" /> --}}
    </x-filament-panels::form>
</x-filament-panels::page>
