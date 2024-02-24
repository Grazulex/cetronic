<div>
    @if ($counter > 0)
        <div class="circle_panier" id="panier_not">{{ $counter }}</div>
    @endif
        <a class="panier" href="{{ route('cart') }}">
            <svg class="profile-fill1" height="25" viewBox="0 0 20 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.64917 9V5.5C5.64917 3.01 7.65917 1 10.1492 1C12.6392 1 14.6492 3.01 14.6492 5.5V9M16.2992 24H3.99919C2.20919 24 0.819175 22.45 1.01917 20.67L2.64918 6H17.6492L19.2792 20.67C19.4792 22.45 18.0892 24 16.2992 24Z" stroke="#4D4D4D" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
</div>

