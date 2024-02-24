@if (count($categories) > 2)
    <section class="section-categories" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
        <div class="container">
            <div class="row mb-5">
                <div class="col-xl-4">
                    <x-categories.picture-component :category="$categories[0]" :key="'picture-' . $categories[0]->id" :id="1" />
                </div>

                <div class="col-xl-8">

                    <x-categories.picture-component :category="$categories[1]" :key="'picture-' . $categories[1]->id" :id="2" />

                    <x-categories.picture-component :category="$categories[2]" :key="'picture-' . $categories[2]->id" :id="3" />

                </div>

            </div>
        </div>
    </section>
@endif
