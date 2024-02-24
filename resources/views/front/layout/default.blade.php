<!DOCTYPE html>
<html lang="{{ App::currentLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @livewireStyles
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.2.0/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.2.0/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="{{ asset('Css/nav.css') }}" />
    <link rel="stylesheet" href="{{ asset('Css/menu.css') }}" />
    <link rel="stylesheet" href="{{ asset('Css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('Css/footer.css') }}" />
    <link rel="stylesheet" href="{{ asset('Css/cart.css') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <title>Cetronic Benelux - @yield('title')</title>
    <meta name="description" content="Cetronic Benelux - @yield('description')">
    <meta name="keywords" content="Cetronic Benelux - @yield('keywords')">
    <meta name="robots" content="index, follow">

    <link rel="alternate" hreflang="nl"
          href="{{ LaravelLocalization::getLocalizedURL('nl') }}" />
    <link rel="alternate" hreflang="fr"
          href="{{ LaravelLocalization::getLocalizedURL('fr') }}" />
    <link rel="alternate" hreflang="x-default"
          href="{{ LaravelLocalization::getLocalizedURL('en') }}" />

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-5NRD2WG');</script>
    <!-- End Google Tag Manager -->
</head>

<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5NRD2WG"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <header class="fixed-top scrolled">
        <x-nav-component />
        <x-menu-component />
    </header>


    <main>
        <div class="main-vspacer"></div>
        @if (session('status'))
        <div class="container">
            <div class="alert alert-success alert-dismissible fade show main-status">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                </button>
            </div>
        </div>
        @endif

        @yield('content')
    </main>
</body>

<x-footer-component />
@livewireScripts
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
</script>

<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.2.0/owl.carousel.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.9/jquery.lazy.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.9/jquery.lazy.plugins.min.js"></script>

<script>
    AOS.init();
</script>


<!-- menu link patch -->
<script>
    // disable link activation when clicking on caret
    var countClick = 0;

    // find all .nav-caret
    var caretElList = document.querySelectorAll(".nav-item.dropdown .nav-caret");
    caretElList.forEach(function(el) {
        // disable propagation on click
        el.addEventListener("click", function(event) {
            event.stopPropagation();
            event.preventDefault();

            // find all link <a> within closest parent of type ".dropdown"
            // then disable click for 500ms
            var elClosestNavItem = el.closest(".dropdown");

            if(elClosestNavItem) {
                var elLinks = elClosestNavItem.querySelectorAll("a");
                elLinks.forEach(function(link) {
                    link.style.pointerEvents = "none";
                })
                countClick++;
                setTimeout(function() {
                    countClick--;

                    if(countClick == 0) {
                        elLinks.forEach(function(link) {
                            link.style.pointerEvents = "auto";
                        })
                    }
                }, 800);
            }
        }, true);
    })
</script>

<!-- cart item removal -->
<script>
    window.addEventListener('cart-item-deleted', event => {
        if(event.detail && event.detail.cart_line_id !== undefined) {
           var trEl = document.querySelector(".table.panier tr[data-id='"+event.detail.cart_line_id + "']");
           if(trEl) {
               trEl.parentElement.removeChild(trEl);
           }
        }
    })
</script>

<!-- scroll -->
<script>
    $(function() {
        $('a[href*=#x-home-item-promo-component]').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $($(this).attr('href')).offset().top
            }, 500, 'linear');
        });
    });
</script>

<!-- owl-carousel -->
<script>
    $(document).ready(function() {

        $(".owl-carousel").owlCarousel({
            items: 4,
            loop: true,
            margin: 10,
            autoplay: true,
            autoplayTimeout: 1000,
            autoplayHoverPause: true,
            responsiveClass: true,
            responsive: {
                0: {
                    items: 1,
                    nav: true
                },
                600: {
                    items: 1,
                    nav: true
                },
                1000: {
                    items: 4,
                    nav: true,
                    loop: true
                }
            }
        });

        $(".owl-prev").html(
            '<span class="pe-2"><img class="img-left" src="https://svgshare.com/i/mAe.svg" /> </span>');
        $(".owl-next").html(
            '<span class="ps-2"> <img class="img-right" src="https://svgshare.com/i/mBY.svg" /></span>');

    });

    jQuery(".owl-items").owlCarousel({
        items: 4,
        loop: true,
        margin: 10,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        touchDrag: false,
        mouseDrag: false,
        responsiveClass: true,
        responsive: {
            0: {
                items: 1,
                nav: true
            },
            600: {
                items: 2,
                nav: false,
            },
            767: {
                items: 3,
                nav: false,
                loop: false
            },
            1000: {
                items: 4,
                nav: false,
                loop: false
            }
        }

    });

    jQuery(".owl-pictures").owlCarousel({
        items: 4,
        loop: true,
        margin: 10,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        touchDrag: false,
        mouseDrag: false,
        responsiveClass: true,
        responsive: {
            0: {
                items: 1,
                nav: true
            },
            600: {
                items: 1,
                nav: false
            },
            1000: {
                items: 1,
                nav: false,
                loop: true
            }
        }
    });
</script>

<!-- Toast -->
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top',
        showConfirmButton: false,
        showCloseButton: true,
        timer: 2000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    window.addEventListener('alert', ({
        detail: {
            type,
            message
        }
    }) => {
        Toast.fire({
            icon: type,
            title: message
        })
    })
</script>

<script>
    $(function() {
        $('.lazy').Lazy();
    });
</script>

</html>
