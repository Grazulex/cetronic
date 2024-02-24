<td>
    <form wire:submit.prevent="removeItem({{ $cartItem->id }})" action="#">
        <button class="btn-delete" type="submit">
            <svg width="19" height="21" viewBox="0 0 19 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16.3 7.6087V18.3478C16.3 19.2565 15.535 20 14.6 20H4.4C3.465 20 2.7 19.2565 2.7 18.3478V7.6087M6.1 10.087V17.5217M12.9 10.087V17.5217M9.5 10.087V17.5217M18 5.95652H1V5.13043C1 4.22174 1.765 3.47826 2.7 3.47826H16.3C17.235 3.47826 18 4.22174 18 5.13043V5.95652ZM13.75 3.47826H5.25C5.25 2.10696 6.389 1 7.8 1H11.2C12.611 1 13.75 2.10696 13.75 3.47826Z" stroke="#4D4D4D" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </form>
</td>
