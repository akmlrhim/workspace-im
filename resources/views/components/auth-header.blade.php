@props(['title', 'description'])

<div class="flex w-full flex-col text-center">
  <flux:heading size="xl">{{ $title }}</flux:heading>
  <flux:subheading size="sm">{{ $description }}</flux:subheading>
</div>
