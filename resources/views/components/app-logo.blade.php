<div {{ $attributes->merge(['class' => 'flex items-center gap-1.5']) }}>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-gray-800 text-gray-100 dark:bg-gray-200 dark:text-gray-900">
        <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="grid flex-1 text-start text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold app-name break-words">{{ config('app.name') }}</span>
    </div>
</div>
