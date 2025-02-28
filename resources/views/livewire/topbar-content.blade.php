<div class="flex items-center px-4">
    <div class="bg-primary-100 dark:bg-primary-800 rounded-2xl px-4 py-2 shadow-sm flex items-center gap-3">
        <div class="flex items-center gap-2">
            <span class="text-primary-600 dark:text-primary-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/>
                    <path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/>
                    <path d="M18 12c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2z"/>
                </svg>
            </span>
            <div class="flex flex-col">
                <span class="text-sm text-primary-600 dark:text-primary-300">الرصيد</span>
                <span class="font-semibold text-primary-700 dark:text-primary-200" dir="rtl">
                    {{ number_format($balance, 2) }} ج.م
                </span>
            </div>
        </div>
        <button
            wire:click="refreshBalance"
            wire:loading.class="animate-spin"
            class="text-primary-600 dark:text-primary-300 hover:text-primary-800 dark:hover:text-primary-100 transition-colors"
            title="تحديث الرصيد"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 2v6h-6"></path>
                <path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path>
                <path d="M3 22v-6h6"></path>
                <path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path>
            </svg>
        </button>
    </div>
</div>
