<div class="animate-pulse">
  {{-- Sticky Header --}}
  <div class="-mx-1 rounded-t-xl border-b border-zinc-200 bg-white px-1 pt-2 dark:border-zinc-700/60 dark:bg-zinc-900">
    <div class="flex items-start justify-between gap-2">
      {{-- Title --}}
      <div class="min-w-0 flex-1 px-2 py-1.5">
        <div class="h-7 w-3/4 rounded-md bg-zinc-200 dark:bg-zinc-700"></div>
      </div>
      {{-- Action buttons placeholder --}}
      <div class="flex shrink-0 items-center gap-1 pt-1.5">
        <div class="h-8 w-8 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
        <div class="h-8 w-8 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
        <div class="h-8 w-8 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
      </div>
    </div>
    {{-- Tabs --}}
    <div class="mt-2 flex items-center gap-1 pb-1">
      <div class="h-9 w-24 rounded-t-md bg-indigo-100 dark:bg-indigo-900/30"></div>
      <div class="h-9 w-24 rounded-t-md bg-zinc-100 dark:bg-zinc-800"></div>
      <div class="h-9 w-20 rounded-t-md bg-zinc-100 dark:bg-zinc-800"></div>
      <div class="h-9 w-20 rounded-t-md bg-zinc-100 dark:bg-zinc-800"></div>
    </div>
  </div>

  {{-- Content --}}
  <div class="space-y-6 pt-6">
    {{-- Properties grid: Status | Prioritas | Tenggat --}}
    <div
      class="grid grid-cols-1 gap-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 sm:grid-cols-3 dark:border-zinc-700/50 dark:bg-zinc-800/20">
      <div class="space-y-2">
        <div class="h-3 w-12 rounded bg-zinc-300 dark:bg-zinc-600"></div>
        <div class="h-8 rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>
      </div>
      <div class="space-y-2">
        <div class="h-3 w-16 rounded bg-zinc-300 dark:bg-zinc-600"></div>
        <div class="h-8 rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>
      </div>
      <div class="space-y-2">
        <div class="h-3 w-14 rounded bg-zinc-300 dark:bg-zinc-600"></div>
        <div class="h-8 rounded-lg bg-zinc-200 dark:bg-zinc-700"></div>
      </div>
    </div>

    {{-- Assignees + Labels --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <div class="space-y-3">
        <div class="flex items-center border-b border-zinc-100 pb-2 dark:border-zinc-700/50">
          <div class="h-4 w-20 rounded bg-zinc-200 dark:bg-zinc-700"></div>
        </div>
        <div class="flex flex-wrap gap-2">
          <div class="h-7 w-28 rounded-full bg-zinc-200 dark:bg-zinc-700"></div>
          <div class="h-7 w-20 rounded-full bg-zinc-200 dark:bg-zinc-700"></div>
        </div>
      </div>
      <div class="space-y-3">
        <div class="flex items-center border-b border-zinc-100 pb-2 dark:border-zinc-700/50">
          <div class="h-4 w-14 rounded bg-zinc-200 dark:bg-zinc-700"></div>
        </div>
        <div class="flex flex-wrap gap-1.5">
          <div class="h-6 w-16 rounded-full bg-zinc-200 dark:bg-zinc-700"></div>
          <div class="h-6 w-20 rounded-full bg-zinc-200 dark:bg-zinc-700"></div>
        </div>
      </div>
    </div>

    {{-- Description --}}
    <div class="space-y-2">
      <div class="h-4 w-24 rounded bg-zinc-200 dark:bg-zinc-700"></div>
      <div class="h-28 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
    </div>

    {{-- Attachments row --}}
    <div class="space-y-2">
      <div class="h-4 w-20 rounded bg-zinc-200 dark:bg-zinc-700"></div>
      <div class="flex gap-2">
        <div class="h-16 w-24 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
        <div class="h-16 w-24 rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
      </div>
    </div>
  </div>
</div>
