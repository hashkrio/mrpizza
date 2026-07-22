@php
    $sizeOrder = ['small', 'medium', 'large'];
    $isValid = fn($v) => $v !== null && $v !== '';
@endphp

@forelse ($items as $item)
    @php
        $detailUrl = route('menu.detail', ['id' => $item['hash']]);
        $img = $item['image'] ?? $fallback;

        // Resolve pricing for display + cart behaviour
        $price = $item['price'];
        $base = null;
        $validCount = 0;
        $isSized = is_array($price);

        if ($isSized) {
            foreach ($sizeOrder as $size) {
                if (isset($price[$size]) && $isValid($price[$size])) {
                    $base = ['size' => $size, 'amount' => $price[$size]];
                    break;
                }
            }
            if (!$base) {
                foreach ($price as $size => $amt) {
                    if ($isValid($amt) && ($base === null || (float) $amt < (float) $base['amount'])) {
                        $base = ['size' => $size, 'amount' => $amt];
                    }
                }
            }
            $validCount = collect($price)->filter($isValid)->count();
        } elseif ($isValid($price)) {
            $base = ['single' => true, 'amount' => $price];
            $validCount = 1;
        }

        // A single, unsized, valid price can be added straight to cart.
        // Sized items (or unpriced) route to the detail page for size selection.
        $canQuickAdd = $base && isset($base['single']);
    @endphp
    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
        <div class="dish-card">
            <a href="{{ $detailUrl }}" class="dish-img">
                <img src="{{ $img }}" alt="{{ $item['name'] }}">
                @if (!empty($item['category']))
                    <span class="dish-cat">{{ $item['category'] }}</span>
                @endif
            </a>
            <div class="dish-body">
                <h4 class="dish-title">
                    <a href="{{ $detailUrl }}">{{ $item['name'] }}</a>
                </h4>
                <div class="dish-price-wrap">
                    @if ($base && isset($base['single']))
                        <span
                            class="dish-price">{{ $symbol }}{{ number_format((float) $base['amount'], 2) }}</span>
                    @elseif ($base)
                        <span class="dish-price">
                            @if ($validCount > 1)
                                <span class="from">{{ __('From') }}</span>
                            @endif
                            {{ $symbol }}{{ number_format((float) $base['amount'], 2) }}
                            <span class="size">({{ ucfirst($base['size']) }})</span>
                        </span>
                    @else
                        <span class="dish-price text-muted">{{ __('Price on request') }}</span>
                    @endif
                </div>

                <div class="d-flex gap-2 mt-2">
                    <a href="{{ $detailUrl }}" class="btn btn-outline-brand btn-sm flex-fill">
                        {{ __('View Details') }}
                    </a>

                    @if ($canQuickAdd)
                        <button type="button" class="btn btn-brand btn-sm js-add-to-cart"
                            data-id="{{ $item['hash'] }}" title="{{ __('Add to Cart') }}">
                            <i class="icon-base ti tabler-shopping-cart-plus"></i>
                        </button>
                    @elseif ($base)
                        {{-- Sized item: send to detail page to choose a size --}}
                        <a href="{{ $detailUrl }}" class="btn btn-brand btn-sm" title="{{ __('Choose a size') }}">
                            <i class="icon-base ti tabler-shopping-cart-plus"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center text-muted py-5">
        <i class="icon-base ti tabler-chef-hat" style="font-size: 3rem;"></i>
        <p class="mt-3">{{ __('No items found in this category yet.') }}</p>
    </div>
@endforelse
