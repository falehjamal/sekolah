@php
  $hasChildren = ! empty($item['children']);
  $classes = collect([
    'menu-item',
    ($item['is_active'] ?? false) ? 'active' : null,
    ($hasChildren && ($item['is_open'] ?? false)) ? 'open' : null,
  ])->filter()->implode(' ');
@endphp

<li class="{{ $classes }}">
  <a
    href="{{ $hasChildren ? 'javascript:void(0);' : $item['url'] }}"
    class="menu-link {{ $hasChildren ? 'menu-toggle' : '' }}"
  >
    <i class="menu-icon tf-icons {{ $item['icon'] }}"></i>
    <div>{{ $item['name'] }}</div>
  </a>

  @if ($hasChildren)
    <ul class="menu-sub">
      @foreach ($item['children'] as $child)
        @include('layouts.partials.sidebar-item', ['item' => $child])
      @endforeach
    </ul>
  @endif
</li>

