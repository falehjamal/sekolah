@php($depth = $depth ?? 0)

@foreach ($items as $item)
    <div class="menu-tree__node" data-depth="{{ $depth }}" data-menu-id="{{ $item['id'] }}">
        <div class="form-check">
            <input
                class="form-check-input menu-checkbox"
                type="checkbox"
                id="menu-{{ $item['id'] }}"
                value="{{ $item['id'] }}"
            >
            <label class="form-check-label d-flex flex-column" for="menu-{{ $item['id'] }}">
                <span class="menu-tree__label">
                    @if (! empty($item['icon']))
                        <i class="{{ $item['icon'] }} me-2"></i>
                    @endif
                    {{ $item['name'] }}
                </span>
                @if (! empty($item['route_name']))
                    <small class="text-muted">{{ $item['route_name'] }}</small>
                @endif
            </label>
        </div>
        @if (! empty($item['children']))
            <div class="menu-tree__children">
                @include('auth.levels.partials.menu-tree', ['items' => $item['children'], 'depth' => $depth + 1])
            </div>
        @endif
    </div>
@endforeach

