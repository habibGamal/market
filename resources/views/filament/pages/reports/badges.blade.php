<div class="flex my-2 gap-4">
    <div class="flex items-center rounded-lg bg-gray-100 px-3 py-2 dark:bg-gray-800">
        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
            {{ number_format($data->total_quantity) }} <br/> قطعة
        </span>
    </div>

    <div class="flex items-center rounded-lg bg-gray-100 px-3 py-2 dark:bg-primary-800">
        <span class="text-xs font-medium text-primary-700 dark:text-primary-300">
            {{ number_format($data->total_value, 2) }} ج.م<br/> مبيعات
        </span>
    </div>

    @isset($data->total_profit)
        <div class="flex items-center rounded-lg bg-gray-100 px-3 py-2 dark:bg-success-800">
            <span class="text-xs font-medium text-success-700 dark:text-success-300">
                {{ number_format($data->total_profit, 2) }} ج.م<br/> ربح
            </span>
        </div>
    @endisset
</div>
