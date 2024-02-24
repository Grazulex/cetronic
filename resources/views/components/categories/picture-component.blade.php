@if ($id > 1)
    <a class="products_link_{{ $id }}" href="{{ route('list', ['cat' => $slug, 'type' => 'category', 'slug' => $slug]) }}">
        <div class="product_cart_contain_{{ $id }}">
            <div class="produits_bg_img_{{ $id }}" style="background: url({{ asset($picture) }})"></div>
            <div class="produits_bg_titre_{{ $id }}">{{ $category->name }}</div>
        </div>
    </a>
@else
    <a class="products_link" href="{{ route('list', ['cat' => $slug, 'type' => 'category', 'slug' => $slug]) }}">
        <div class="product_cart_contain">
            <div class="produits_bg_img" style="background: url({{ asset($picture) }})"></div>
            <div class="produits_bg_titre">{{ $category->name }}</div>
        </div>
    </a>
@endif
