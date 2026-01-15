<div 
    x-show="modalOpen" 
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto" 
    aria-labelledby="modal-title" 
    role="dialog" 
    aria-modal="true"
    @open-modal.window="modalOpen = true">
    
    <!-- Overlay -->
    <div 
        x-show="modalOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        @click="modalOpen = false"></div>

    <!-- Modal Content -->
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div 
            x-show="modalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl">
            
            <!-- Header Modal -->
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Novo Lead</h3>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Iframe com formulário -->
                <div class="max-h-[70vh] overflow-y-auto">
                    <iframe 
                        src="{{ route('leads.create') }}" 
                        class="w-full h-[70vh] border-0"
                        id="createLeadFrame"
                        @load="setupIframe()">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setupIframe() {
    window.addEventListener('message', function(event) {
        if (event.data === 'lead-created') {
            window.location.reload();
        }
        
        if (event.data === 'lead-cancelled') {
            Alpine.store('modal', false);
        }
    });
}

// Fechar modal com ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modalOpen = document.querySelector('[x-data]').__x?.$data.modalOpen;
        if (modalOpen) {
            document.querySelector('[x-data]').__x?.$data.modalOpen = false;
        }
    }
});
</script>
