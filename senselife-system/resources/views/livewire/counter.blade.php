<div class="rounded-lg border border-gray-200 p-6 shadow-sm dark:border-gray-600">
    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Counter</h2>
    <p class="mb-4 text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $count }}</p>
    <button
        type="button"
        wire:click="increment"
        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white"
    >
        Increment
    </button>
</div>
