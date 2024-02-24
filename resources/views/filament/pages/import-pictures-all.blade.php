<x-filament::page>
    <form wire:submit.prevent="submit" x-data="{ open: false }">
        {{ $this->form }}
        <br />
        <button @click="open = true" type="submit" class="inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset filament-button min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 filament-page-button-action">
            Upload
        </button>
        <br />
        <div x-show="open" class="text-xl text-green-500">
            Uploading Busy...
        </div>
    </form>
</x-filament::page>
