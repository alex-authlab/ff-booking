<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php
language_attributes(); ?>>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="Imagetoolbar" content="No"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    if ($settings['featured_image']): ?>
        <meta property="og:image" content="<?php
        echo $settings['featured_image']; ?>">
    <?php
    endif; ?>
    <?php
    if ($settings['description']): ?>
        <meta property="og:description" content="<?php
        echo strip_tags($settings['description']); ?>">
    <?php
    endif; ?>
    <?php
    wp_head();
    ?>

    <style type="text/css">
        <?php if($settings['background_image']): ?>
        body.ff_landing_page_body {
            background-image: url("<?php echo  $settings['background_image']; ?>") !important;
            background-repeat: no-repeat !important;
            background-size: cover !important;
            background-position: center center !important;
            background-attachment: fixed;
        }

        body.ff_landing_page_body::after {
            background-color: #6f6f6f;
            content: "";
            display: block;
            position: fixed;
            top: 0px;
            left: 0px;
            width: 100%;
            z-index: -1;
            opacity: 0.4;
            bottom: 0;
            right: 0;
        }

        <?php endif; ?>
        body.ff_landing_page_body {
            line-height: 1.65714285714286;
        }
    </style>
    <style id="ff_landing_css" type="text/css">
        body.ff_landing_page_body {
            border-top-color: <?php echo $bg_color; ?> !important;
            background-color: <?php echo $bg_color; ?>;
        }
    </style>
</head>
<body class="ff_landing_page_body ff_landing_page_booking">

<div class="ff_landing_wrapper ff_landing_design_<?php
echo $settings['design_style']; ?>">
    <div class="ff_landing_form">
        <?php
        if ($has_header): ?>
            <div class="ff_landing_header">
                <?php
                if ($settings['logo']): ?>
                    <div class="ff_landing-custom-logo">
                        <img src="<?php
                        echo $settings['logo']; ?>" alt="Form Logo">
                    </div>
                <?php
                endif; ?>
                <?php
                if ($settings['title']): ?>
                    <h1><?php
                        echo $settings['title']; ?></h1>
                <?php
                endif; ?>
                <?php
                if ($settings['description']): ?>
                    <div class="ff_landing_desc">
                        <?php
                        echo $settings['description']; ?>
                    </div>
                <?php
                endif; ?>
            </div>
        <?php
        endif; ?>
        <div class="ff_landing_body">

                <div class="ff_booking_container">
                    <div class="ff_booking_view_holder">
                        <div class="ff_booking_content">
                            <div class="ff_booking_info with-border">
                                <h1>Booking : <span><?php
                                        echo ucfirst($data['booking_status']) ?>  </span></h1>
                                <p>Appointment for <b><?php
                                        echo ucfirst($data['service']) ?> </b> by <b><?php
                                        echo ucfirst($data['provider']) ?></b></p>
                                <?php
                                if ($data['description'] != '') {
                                    echo "<p>{$data['description']}</p>";
                                } ?>

                                <div class="ff_booking_bttns">
                                    <?php
                                    if ($data['isProviderLoggedin']): ?>
                                        <a class="ffb_bttns resched-bttn" href="#">Reschedule</a>
                                    <?php
                                    elseif ($data['booking_status'] != 'canceled'): ?>

                                        <?php
                                        if ($data['allowUserCancel']) { ?>
                                            <a class="ffb_bttns resched-bttn" href="#">Reschedule</a>

                                            <?php
                                        }
                                        if ($data['allowUserReschedule']) {
                                            ?>
                                            <a class="ffb_bttns cancel-bttn" href="Ok">Cancel</a>

                                            <?php
                                        } endif; ?>


                                </div>
                                <span class="ffb_disclaimer"> <?php echo $data['policy'] ?></span>
                            </div>
                            <div class="ff_booking_info ">
                                <ul class="ff_bk_info_list">
                                    <li>
                                        <svg xmlns="http://www.w3.org/2000/svg" height="28px" width="28px">
                                            <path d="M 14 2 C 13.448 2 13 2.448 13 3 L 13 4.1914062 C 7.8890453 5.1364053 4 9.6205775 4 15 C 4 21.063288 8.9367124 26 15 26 C 21.063288 26 26 21.063288 26 15 C 26 9.6205775 22.110955 5.1364053 17 4.1914062 L 17 3 C 17 2.448 16.552 2 16 2 L 14 2 z M 22.990234 3.9902344 A 1.0001 1.0001 0 0 0 22.292969 5.7070312 L 23.292969 6.7070312 A 1.0001 1.0001 0 1 0 24.707031 5.2929688 L 23.707031 4.2929688 A 1.0001 1.0001 0 0 0 22.990234 3.9902344 z M 15 6 C 19.982407 6 24 10.017593 24 15 C 24 19.982407 19.982407 24 15 24 C 10.017593 24 6 19.982407 6 15 C 6 10.017593 10.017593 6 15 6 z M 14.984375 7.9863281 A 1.0001 1.0001 0 0 0 14 9 L 14 13.271484 A 2 2 0 0 0 13 15 A 2 2 0 0 0 14 16.730469 L 14 18 A 1.0001 1.0001 0 1 0 16 18 L 16 16.728516 A 2 2 0 0 0 17 15 A 2 2 0 0 0 16 13.269531 L 16 9 A 1.0001 1.0001 0 0 0 14.984375 7.9863281 z"></path>
                                        </svg>
                                        <?php
                                        echo date(
                                            get_option('time_format'),
                                            strtotime($data['booking_time'])
                                        ); ?>
                                    </li>
                                    <li>
                                        <svg xmlns="http://www.w3.org/2000/svg" height="28px" width="28px">
                                            <path d="M 6 1 L 6 3 L 5 3 C 3.9 3 3 3.9 3 5 L 3 19 C 3 20.1 3.9 21 5 21 L 19 21 C 20.1 21 21 20.1 21 19 L 21 5 C 21 3.9 20.1 3 19 3 L 18 3 L 18 1 L 16 1 L 16 3 L 8 3 L 8 1 L 6 1 z M 5 5 L 6 5 L 8 5 L 16 5 L 18 5 L 19 5 L 19 7 L 5 7 L 5 5 z M 5 9 L 19 9 L 19 19 L 5 19 L 5 9 z"></path>
                                        </svg>
                                        <?php
                                        echo date('l F j Y', strtotime($data['booking_date'])); ?>
                                    </li>
                                    <li>
                                        <svg xmlns="http://www.w3.org/2000/svg" height="28px" width="28px">
                                            <path d="M 12 2 C 6.486 2 2 6.486 2 12 C 2 17.193 5.9807812 21.470172 11.050781 21.951172 C 10.385781 20.784172 10 19.439 10 18 C 10 17.917 10.009719 17.837859 10.011719 17.755859 C 9.7987187 17.245859 9.6043125 16.661 9.4453125 16 L 10.261719 16 C 10.444719 15.29 10.719125 14.619 11.078125 14 L 9.1074219 14 C 9.0404219 13.371 9 12.707 9 12 C 9 11.293 9.0404219 10.629 9.1074219 10 L 14.892578 10 C 14.913578 10.197 14.925406 10.403422 14.941406 10.607422 C 15.561406 10.350422 16.223156 10.176031 16.910156 10.082031 C 16.908156 10.055031 16.90925 10.025047 16.90625 9.9980469 L 19.736328 9.9980469 C 19.753328 10.065047 19.761344 10.135125 19.777344 10.203125 C 20.551344 10.379125 21.282125 10.666828 21.953125 11.048828 C 21.471125 5.9798281 17.193 2 12 2 z M 12 4 C 12.843 4 13.948688 5.481 14.554688 8 L 9.4453125 8 C 10.051312 5.481 11.157 4 12 4 z M 8.4394531 4.8457031 C 7.9924531 5.7347031 7.6356719 6.801 7.3886719 8 L 5.0820312 8 C 5.8680313 6.648 7.0354531 5.5477031 8.4394531 4.8457031 z M 15.560547 4.8457031 C 16.963547 5.5477031 18.131969 6.648 18.917969 8 L 16.613281 8 C 16.366281 6.801 16.007547 5.7347031 15.560547 4.8457031 z M 4.2636719 10 L 7.09375 10 C 7.03375 10.643 7 11.311 7 12 C 7 12.689 7.03375 13.357 7.09375 14 L 4.2636719 14 C 4.0976719 13.359 4 12.692 4 12 C 4 11.308 4.0976719 10.641 4.2636719 10 z M 18 12 C 14.7 12 12 14.7 12 18 C 12 21.3 14.7 24 18 24 C 21.3 24 24 21.3 24 18 C 24 14.7 21.3 12 18 12 z M 18 14 C 20.2 14 22 15.8 22 18 C 22 20.2 20.2 22 18 22 C 15.8 22 14 20.2 14 18 C 14 15.8 15.8 14 18 14 z M 18.427734 15.117188 L 16.828125 18.138672 L 18.925781 20.236328 L 19.986328 19.175781 L 18.671875 17.861328 L 19.753906 15.818359 L 18.427734 15.117188 z M 5.0820312 16 L 7.3867188 16 C 7.6337187 17.199 7.9924531 18.265297 8.4394531 19.154297 C 7.0364531 18.452297 5.8680313 17.352 5.0820312 16 z"></path>
                                        </svg>
                                        <?php
                                        echo FF_Booking\Booking\BookingHelper::getTimeZone(); ?>

                                    </li>
                                </ul>
                            </div>

                        </div>

                        <?php
                        if ($data['allowUserReschedule'] || $data['isProviderLoggedin']) { ?>
                            <div class="ff_booking_content ffb_form">

                                <div class="ff_booking_info ">
                                    <input type="text" class="ffb-input" id="ffb_view_picker">
                                    <label for="reason">Reason for change</label>
                                    <textarea class="ffb-input" name="reason" id="ffs-reason-text" cols="30" rows="3"></textarea>
                                    <button class="ffb_bttns ffb-submit-bttn ffb-input">Submit</button>

                                </div>
                                <div class="ff_booking_info slot">
                                    <div class="ff_bk_info_list ff-time-slot-container">

                                    </div>
                                    <div class="ff-time-slot-details"></div>
                                </div>

                            </div>
                        <?php
                        } ?>
                        <div class="user_cancel_confirm" style="display: none">
                            <?php echo __('Are You sure to cancel this booking?',FF_BOOKING_SLUG)?>
                            <div class="ff_booking_bttns">
                                <button class="ffb_bttns cancel-confirm" >
                                    <?php echo __('Confirm',FF_BOOKING_SLUG)?>
                                </button>
                                <button class="ffb_bttns close-confirm" >
                                    <?php echo __('No',FF_BOOKING_SLUG)?>
                                </button>
                            </div>

                        </div>
                    </div>


                </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        function initPicker() {
            if(typeof flatpickr == 'undefined') {
                return;
            }
            let targetFp;
            var config = {
                inline:true,
            }
            if (!config.locale) {
                config.locale = 'default';
            }
            if(jQuery("#ffb_view_picker").length) {
                targetFp =  flatpickr('#ffb_view_picker', config);

            }
        }
        initPicker();

        jQuery('.ffb_bttns.resched-bttn').on('click',function(e){
            e.preventDefault();
            jQuery('.ff_booking_content.ffb_form').slideDown().css('display','flex');
        })

    });
</script>
<?php
wp_footer();
?>
</body>
</html>

