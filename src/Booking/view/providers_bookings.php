<?php
/** @var Array $bookings */

/** @var Array $config */
?>
<div class="ff_bookings_container">
    <div class="ff_booking_filters">
        <div class="ffs_booking_btns">
            <?php
            foreach ($config['get_filters'] as $key=> $label){
                $active = '';
                if($key == $config['filterStatus']){
                  $active = 'ffs_active';
                }
                echo "<a href='?status={$key}' class='ffs_link {$active}'>{$label}</a>";
            }
            ?>

        </div>
    </div>
    <?php
    if (count($bookings) == 0) {
        ?>
        <div class="ff_booking"><p> <?php _e('No Bookings Found', FF_BOOKING_SLUG); ?></p></div>
        <?php
    } else {
        foreach ($bookings as $date => $bookingGroup): ?>
            <div class="ffs_booking_date_grp"> <?php echo $date ?></div>

            <?php
            foreach ($bookingGroup as $booking) { ?>
                <div class="ff_booking">
                    <div class="ff_booking_header">
                        <div class="ff_booking_info">
                            <span class="ffs_service_badge" style="background: <?php echo $booking['color']?>"></span>
                            <span class="ffs_service_name"><?php echo $booking['service']; ?></span>
                        </div>
                        <div class="ff_booking_info">
                            <div >

                            <span class="ff_booking_status_badge ff_booking_status_<?php echo $booking['booking_status']; ?>">
                                <?php echo $booking['booking_status']; ?>
                            </span>
                            </div>
                        </div>

                        <div class="ff_booking_info">
                                <?php echo $booking['booking_time']; ?>
                        </div>
                        <div class="ff_booking_info">
                                <?php echo $booking['duration']; ?>
                        </div>
                        <div class="ff_booking_info ffs-pull-right">
                            <div class="ffs_booking_btns">
                                <button class="ffs_link ffs_details"> Details <span class="ffs_details_icon">+</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div  class="ffs_booking_details_text" style="display: none">

                            <div class="ffs_details_info">
                                <span> <b><?php _e('Name', FF_BOOKING_SLUG); ?></b> <?php print $booking['name']?></span> <br>
                                <span> <b><?php _e('Email', FF_BOOKING_SLUG); ?></b> <?php print $booking['email']?></span>
                            </div>

                            <div class="ffs_details_info">
                                <b><?php _e('Notes', FF_BOOKING_SLUG); ?></b>
                                <span> <?php echo $booking['notes']?></span>
                            </div>

                            <div class="ffs_details_info">
                              <small> <?php _e('Created', FF_BOOKING_SLUG); ?> <?php echo $booking['created_at']?></small>
                            </div>

                            <div class="ffs_details_info_actions ffs_booking_btns">
                                <span><?php _e('Change status', FF_BOOKING_SLUG); ?></span>
                                <select name="" class="ffs_link ffs_booking_status" data-booking_id="<?php echo $booking['id'] ?> ">
                                    <?php foreach ($config['booking_status'] as $key => $status) {
                                        if($key == $booking['booking_status']){
                                            $selected ='selected';
                                        }else{
                                            $selected ='';
                                        }
                                        ?>
                                        <option <?php echo $selected?> value="<?php echo $key ?>" >
                                            <?php echo $status?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <a class="ffs_confirm ffs_link" href="<?php echo $config['base_url'].'?ff_simple_booking='.$booking['booking_hash'] ?>">
                                    <?php echo $config['reschulde_btn']; ?>
                                </a>

                            </div>

                    </div>
                    <div  class="ffs_booking_details ffs_booking_action_confirmation" style="display: none">
                        <h4><?php
                            echo $config['confirm_heading']; ?></h4>
                        <div class="ffs_booking_btns">
                            <button class="ffs_confirm ffs_link"><?php
                                echo $config['confirm_btn']; ?></button>
                            <button class="ffs_close ffs_link"><?php
                                echo $config['close']; ?></button>
                            <p class="ffs_message_notices"></p>
                        </div>
                    </div>
                </div>

            <?php
            } ?>
        <?php
        endforeach;
    } ?>
</div>
