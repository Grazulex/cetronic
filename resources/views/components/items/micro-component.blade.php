<div>
    <div itemtype="https://schema.org/Product" itemscope>
        <meta itemprop="mpn" content="{{ $item->reference }}" />
        <meta itemprop="name" content="{{ $item->brand->name }} - {{ $item->reference }}" />
        @if ( Str::length($item->description) > 0)
            <meta itemprop="description" content="{{ trim($item->microDescription) }}" />
        @else
            <meta itemprop="description" content="{{ trim($item->brand->microDescription) }} - {{ $item->reference }}" />
        @endif
        @foreach ($item->getMedia() as $media)
               <link itemprop="image" href="{{ $media->getUrl() }}" />
        @endforeach
        <div itemprop="aggregateRating" itemtype="https://schema.org/AggregateRating" itemscope>
            <meta itemprop="reviewCount" content="100" />
            <meta itemprop="ratingValue" content="5" />
        </div>
        <div itemprop="review" itemtype="https://schema.org/Review" itemscope>
            <div itemprop="author" itemtype="https://schema.org/Person" itemscope>
                <meta itemprop="name" content="Bhamani Geoffrey" />
            </div>
            <div itemprop="reviewRating" itemtype="https://schema.org/Rating" itemscope>
                <meta itemprop="ratingValue" content="5" />
                <meta itemprop="bestRating" content="5" />
            </div>
        </div>
        @foreach ($item->metas as $meta)
            <div itemprop="exifData" itemscope itemtype="https://schema.org/PropertyValue">
                <meta itemprop="name" content="{{ $meta->meta->name }}">
                <meta itemprop="value" content="{{ $meta->value }}">
            </div>
        @endforeach
        <div itemprop="offers" itemtype="https://schema.org/Offer" itemscope>
            <link itemprop="url" href="{{ url()->current() }}" />
            <meta itemprop="category" content="{{ $item->category->name }}" />
            @if ($item->is_published)
                <meta itemprop="availability" content="https://schema.org/OnlineOnly" />
            @else
                <meta itemprop="availability" content="https://schema.org/OutOfStock" />
            @endif
            <meta itemprop="priceCurrency" content="EUR" />
            <meta itemprop="itemCondition" content="https://schema.org/NewCondition" />
            <meta itemprop="price" content="{{ $price['sale'] }}" />
            <meta itemprop="priceValidUntil" content="{{ now()->addDays(30)->format('Y-m-d') }}" />
        </div>
        <div itemprop="brand" itemtype="https://schema.org/Brand" itemscope>
            <meta itemprop="name" content="{{ $item->brand->name }}" />
        </div>
    </div>
</div>
