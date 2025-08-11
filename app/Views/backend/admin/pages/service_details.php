<div class="main-content">
    <section class="section">
        <div class="section-header mt-2">
            <h1><?= labels('view_service', "View Service") ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/admin/services') ?>"><i class="	fas fa-tools text-warning"></i> <?= labels('service', 'Service') ?></a></div>
                <div class="breadcrumb-item"><?= labels('view_service', 'View Service') ?></a></div>

            </div>
        </div>

        <div class="row  mb-4">
            <div class="col-md-12 col-xl-8 col-sm-12">
                <div class="card d-flex flex-column h-100 ">
                    <div class="row pl-3 border_bottom_for_cards m-0">
                        <div class="col-auto ">
                            <div class="toggleButttonPostition"><?= labels('service_details', 'Service Details') ?></div>
                        </div>

                        <div class="col d-flex justify-content-end mr-3 mt-4">
                            <?php
                            $label = ($service[0]['status'] == 1) ?
                                "<div class='tag border-0 rounded-md  bg-emerald-success text-emerald-success mx-2'>" . labels('active', 'Active') ."</div>" :
                                "<div class='tag border-0 rounded-md  bg-emerald-danger text-emerald-danger mx-2'>". labels('deactive', 'Deactive') ."</div>";

                            echo $label;
                            ?>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row mb-3">

                            <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                <div class="icon_box">
                                    <i class="fa-solid fa-user text-white"></i>
                                </div>
                                <div class="service_info">
                                    <span class="title"><?= labels('provider', 'Provider') ?></span>
                                    <p class="m-0"><?= $service[0]['user_id'] ?></p>
                                </div>
                            </div>


                            <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                <div class="icon_box">
                                    <i class="	fas fa-tools fa-lg text-white"></i>
                                </div>
                                <div class="service_info">
                                    <span class="title"><?= labels('title', 'Title') ?></span>
                                    <p class="m-0"><?= $service[0]['title'] ?></p>
                                </div>
                            </div>


                            <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                <div class="icon_box">
                                    <i class="fa-solid fa-info text-white"></i>
                                </div>
                                <div class="service_info">
                                    <span class="title"><?= labels('slug', 'Slug') ?></span>
                                    <p class="m-0"><?= $service[0]['slug'] ?></p>
                                </div>
                            </div>




                        </div>

                        <div class="row mb-3">
                            <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                <div class="icon_box">
                                    <i class="fa-solid fa-list text-white"></i>
                                </div>
                                <div class="service_info">
                                    <span class="title"><?= labels('category', 'Category') ?></span>
                                    <p class="m-0"><?= $service[0]['category_id'] ?></p>
                                </div>
                            </div>

                            <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                <div class="icon_box">
                                    <i class="fa-solid fa-percent text-white"></i>
                                </div>

                                <div class="service_info">
                                    <span class="title"><?= labels('tax_type', 'Tax Type') ?></span>
                                    <p class="m-0"><?= $service[0]['tax_type'] ?></p>
                                </div>

                            </div>


                            <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                <div class="icon_box">

                                    <i class="fa-solid fa-people-carry-box text-white"></i>
                                </div>

                                <div class="service_info">

                                    <span class="title"><?= labels('members_required', 'Members required') ?></span>
                                    <p class="m-0"><?= $service[0]['number_of_members_required'] ?></p>
                                </div>

                            </div>
                        </div>
                        <div class="row mb-3">

                            <div class="col-xl-4 col-md-4 col-sm-6 mb-sm-2 d-flex">
                                <div class="icon_box">
                                    <i class="fas fa-calculator text-white"></i>
                                </div>

                                <div class="service_info">
                                    <span class="title"><?= labels('max_quantity_allowed', 'Max quantity allowed') ?></span>
                                    <p class="m-0"><?= $service[0]['max_quantity_allowed'] ?></p>
                                </div>

                            </div>


                        </div>




                        <div class="row mb-3">

                            <div class="col-xl-12 col-md-12 col-sm-12 mb-sm-2 d-flex">
                                <div class="icon_box">

                                    <i class="fas fa-book text-white"></i>
                                </div>

                                <div class="service_info">
                                    <span class="title"><?= labels('description', 'Description') ?></span>
                                    <p class="m-0"><?= $service[0]['description'] ?></p>
                                </div>

                            </div>





                        </div>

                        <div class="row ">

                            <div class="col-xl-12 col-md-12 col-sm-12 mb-sm-2 d-flex">
                                <div class="icon_box">
                                    <i class="fas fa-quote-left text-white"></i>
                                </div>
                                <div class="service_info">
                                    <span class="title"><?= labels('long_description', 'Long Description') ?></span>
                                    <p class="m-0"><?= $service[0]['long_description'] ?></p>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>
            </div>
            <div class="col-md-12 col-xl-4 col-sm-12 ">
                <div class="card d-flex flex-column h-100">
                    <div class="row pl-3 border_bottom_for_cards m-0">
                        <div class="col-auto">
                            <div class="toggleButttonPostition"><?= labels('basic_details', 'Basic Details') ?></div>
                        </div>


                    </div>

                    <div class="card-body">
                        <div class="row mb-3">

                            <div class="col-xl-12 col-md-12 col-sm-12 mb-sm-2">
                                <div class="col-xl-12 col-md-12">
                                    <span class="ml-2"><?= labels('image', 'Image') ?></span>
                                </div>

                                <div class="col-xl-12 col-md-12">
                                    <img alt="no image found" width="130px" style="border: solid 1; border-radius: 12px;" height="100px" class="mt-2" id="image_preview" src="<?= isset($service[0]['image']) ? ($service[0]['image']) : "" ?>">
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-6 col-sm-12 mb-sm-2 d-flex">
                                <div class="icon_box">

                                    <i class="fas fa-clock text-white"></i>
                                </div>

                                <div class="service_info">
                                    <span class="title"><?= labels('duration', 'Duration') ?></span>
                                    <p class="m-0"><?= $service[0]['duration'] ?></p>

                                </div>

                            </div>

                            <div class="col-xl-6 col-md-6 col-sm-12 mb-sm-2 d-flex">
                                <div class="icon_box">
                                    <i class="fas fa-coins text-white"></i>
                                </div>
                                <div class="service_info">
                                    <span class="title"><?= labels('price', 'Price') ?></span>
                                    <p class="m-0"><?= $service[0]['price'] ?></p>
                                </div>
                            </div>

                            <div class="col-xl-6 col-md-6 col-sm-12 mb-sm-2 d-flex">
                                <div class="icon_box">

                                    <i class="fas fa-money-bill-wave text-white"></i>
                                </div>

                                <div class="service_info">
                                    <span class="title"><?= labels('discount_price', 'Discount Price') ?></span>
                                    <p class="m-0"><?= $service[0]['discounted_price'] ?></p>

                                </div>

                            </div>



                        </div>

                        <div class="row mb-3">

                            <div class="col-xl-6 col-md-6 col-sm-12 mb-sm-2 d-flex">
                                <div class="icon_box">

                                    <i class="fas fa-info-circle text-white"></i>
                                </div>

                                <div class="service_info">
                                    <span class="title"><?= labels('is_cancelable', 'Is Cancelable ') ?></span>
                                    <p class="m-0">
                                        <?php
                                        $is_cancellable_badge = ($service[0]['is_cancelable'] == 1) ?
                                            "<div class='text-emerald-success ml-3 mr-3 m-0'>Yes</div>" :
                                            "<div class='text-emerald-danger ml-3 mr-3 m-0'>No</div>";

                                        echo $is_cancellable_badge;
                                        ?>
                                    </p>
                                </div>
                            </div>



                            <div class="col-xl-6 col-md-6 col-sm-12 mb-sm-2 d-flex">
                                <div class="icon_box">

                                    <i class="fas fa-info-circle text-white"></i>
                                </div>

                                <div class="service_info">

                                    <span class="title"><?= labels('cancelable_till', 'Cancelable before') ?></span>

                                    <p class="m-0">
                                        <?= $service[0]['cancelable_till'] ?>

                                    </p>


                                </div>

                            </div>


                        </div>

                        <div class="row">
                            <div class="col-xl-6 col-md-6 col-sm-12 mb-sm-2 d-flex">
                                <div class="icon_box">

                                    <i class="fas fa-info-circle text-white"></i>
                                </div>

                                <div class="service_info">
                                    <span class="title"><?= labels('at_store', 'At Store') ?></span>
                                    <p class="m-0">
                                        <?php
                                        $is_cancellable_badge = ($service[0]['at_store'] == 1) ?
                                            "<div class='text-emerald-success ml-3 mr-3 m-0'>Yes</div>" :
                                            "<div class='text-emerald-danger ml-3 mr-3 m-0'>No</div>";

                                        echo $is_cancellable_badge;
                                        ?>
                                    </p>
                                </div>
                            </div>

                            <div class="col-xl-6 col-md-6 col-sm-12 mb-sm-2 d-flex">
                                <div class="icon_box">

                                    <i class="fas fa-info-circle text-white"></i>
                                </div>

                                <div class="service_info">
                                    <span class="title"><?= labels('at_doorstep', 'At Doorstep') ?></span>
                                    <p class="m-0">
                                        <?php
                                        $is_cancellable_badge = ($service[0]['at_store'] == 1) ?
                                            "<div class='text-emerald-success ml-3 mr-3 m-0'>Yes</div>" :
                                            "<div class='text-emerald-danger ml-3 mr-3 m-0'>No</div>";

                                        echo $is_cancellable_badge;
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-xl-8 col-sm-12">
                <div class="card  d-flex flex-column h-100">
                    <div class="row pl-3 border_bottom_for_cards m-0">
                        <div class="col-auto">
                            <div class="toggleButttonPostition"><?= labels('faqs', 'Faqs') ?></div>
                        </div>


                    </div>
                    <?php

                    if (!empty($service[0]['faqs'])) {

                        $faqs = json_decode($service[0]['faqs'], true); // Decode JSON into a PHP array

                        echo '<main>';
                        foreach ($faqs as $index => $faq) {
                            $question = $faq[0];
                            $answer = $faq[1];
                            echo '<div class="topic">';
                            echo '<div class="open1">';
                            echo '<h2 class="question">' . ($index + 1) . '. ' . $question . '</h2>';
                            echo '<span class="faq-t"></span>';
                            echo '</div>';
                            echo '<p class="answer">' . $answer . '</p>';
                            echo '</div>';
                        }
                        echo '</main>';
                    } else {
                        echo '     <div class="col-md-12 d-flex justify-content-center">

                        <!-- <h5>No data found</h5> -->


                        <div class="empty-state" data-height="400" style="height: 400px;">
                            <div class="empty-state-icon bg-primary">
                                <i class="fas fa-question text-white "></i>
                            </div>
                            <h2>'. labels("we_couldnt_find_any_providers", "We couldn't find any providers") .'</h2>
                            <p class="lead">
                                '. labels("sorry_we_cant_find_any_data", "Sorry we can't find any data, to get rid of this message, make at least 1 entry.") .
                            '</p>

                        </div>
                    </div>';
                    }
                    ?>


                </div>
            </div>

            <div class="col-md-12 col-xl-4 col-sm-12">
                <div class="card d-flex flex-column h-100 ">
                    <div class="row pl-3 border_bottom_for_cards m-0">
                        <div class="col ">
                            <div class="toggleButttonPostition"><?= labels('other_images', 'Other Images') ?></div>
                        </div>

                    </div>

                    <div class="card-body">

                        <?php

                        if (!empty($service[0]['other_images'])) {
                            $other_images=$service[0]['other_images'];
                        ?>
                            <div class="row">

                                <?php foreach ($other_images as $row) { ?>

                                    <div class="col-xl-4 col-md-12">
                                        <img alt="no image found" width="130px" style="border: solid 1; border-radius: 12px;" height="100px" class="mt-2" id="image_preview" src="<?= isset($row) ? ($row) : "" ?>">
                                    </div>
                            <?php }
                            } else {
                                echo '     <div class="col-md-12 d-flex justify-content-center">

                                <!-- <h5>No data found</h5> -->


                                <div class="empty-state" data-height="400" style="height: 400px;">
                                    <div class="empty-state-icon bg-primary">
                                        <i class="fas fa-question text-white "></i>
                                    </div>
                                    <h2>We couldn\'t find any Providers</h2>
                                    <p class="lead">
                                        Sorry we can\'t find any data, to get rid of this message, make at least 1 entry.
                                    </p>

                                </div>
                            </div>';
                            }
                            ?>
                            </div>





                    </div>

                </div>


            </div>
        </div>


</div>


<div class="row">

</div>


</div>

</div>
</div>
</section>
</div>

<script>
    $(".open1").click(function() {
        var container = $(this).parents(".topic");
        var answer = container.find(".answer");
        var trigger = container.find(".faq-t");

        answer.slideToggle(200);

        if (trigger.hasClass("faq-o")) {
            trigger.removeClass("faq-o");
        } else {
            trigger.addClass("faq-o");
        }

        if (container.hasClass("expanded")) {
            container.removeClass("expanded");
        } else {
            container.addClass("expanded");
        }
    });
</script>