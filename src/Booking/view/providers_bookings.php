<?php
/** @var Array $bookings */

/** @var Array $config */
?>
<div class="ff_bookings_wrapper">

    <div class="ff_bookings_container">
        <ul class="ffsb_tabs ffs_booking_btns">
            <li><a class="ffs_link <?php if($config['activeTab'] == 'bookings' ){echo 'ffs_active';} ?>"    href='<?php echo $config['base_url'].'route=bookings' ?>' >Bookings</a></li>
            <?php if(\FluentForm\Framework\Helpers\ArrayHelper::exists($config,'gCalendarData')) { ?>
            <li><a class="ffs_link  <?php if($config['activeTab'] == 'google_calendar' ){echo 'ffs_active';} ?>"  href='<?php echo $config['base_url'].'route=google_calendar' ?>'>Google Calendar</a></li>
            <?php }?>

        </ul>
        <?php  if($config['activeTab'] == 'bookings' ): ?>
        <div class="ffsb_tab_body">
            <h4><?php echo $config['booking_title'] ?></h4>
            <div class="ff_booking_filters">
                <div class="ffs_booking_btns">
                    <?php
                    foreach ($config['get_filters'] as $key=> $label){
                        $active = '';
                        if($key == $config['filterStatus']){
                            $active = 'ffs_active';
                        }
                        echo "<a href='{$config['base_url']}status={$key}' class='ffs_link {$active}'>{$label}</a>";
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
                                    <?php if ($booking['booking_type'] =='time_slot') {echo $booking['booking_time'];} ?>
                                </div>
                                <div class="ff_booking_info">
                                    <?php if($booking['booking_type'] =='date_slot'){
                                        _e('Full Day',FF_BOOKING_SLUG);
                                    }elseif ($booking['booking_type'] =='time_slot'){
                                        echo $booking['duration'];
                                    }
                                    ?>
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
                                    <b><?php _e('Name', FF_BOOKING_SLUG); ?></b>
                                    <p> <?php echo empty($booking['name'])? '-' :$booking['name'] ?></p>
                                    <b><?php _e('Email', FF_BOOKING_SLUG); ?></b>
                                    <p><?php echo empty($booking['email'])? '-' :$booking['email']  ?></p>
                                    <div class="ffs_notes">
                                        <b><?php _e('Form Name', FF_BOOKING_SLUG); ?></b>
                                        <p><?php echo ($booking['form_title']) ?>
                                        </p>
                                        <b><?php _e('Notes', FF_BOOKING_SLUG); ?></b>
                                        <p><?php echo empty($booking['notes'])? 'No Notes': $booking['notes'] ?>
                                        </p>
                                        <div class="ffs_booking_btns">
                                            <button class="ffs_edit_note ffs_link">
                                                <?php _e("Edit Note",FF_BOOKING_SLUG); ?>
                                            </button>
                                        </div>
                                        <div class="ffs_bookings_notes" style=" display:none">
                                            <textarea name="notes"  class="edit-notes" cols="2" rows="2"><?php echo trim($booking['notes']); ?></textarea>

                                            <button data-booking_id="<?php echo $booking['id'] ?> " class="ffs_update_note ffs_link">
                                                <?php _e("Save Note",FF_BOOKING_SLUG); ?>
                                            </button>
                                        </div>
                                        <div class="ffs_note_response"></div>

                                    </div>
                                    <?php if( is_array($addonData = \FluentForm\Framework\Helpers\ArrayHelper::get($booking,'addon_data') )){ ?>

                                        <?php if( $gCalendar = \FluentForm\Framework\Helpers\ArrayHelper::get($addonData,'google_calendar')){
                                            $eventLink = \FluentForm\Framework\Helpers\ArrayHelper::get($gCalendar,'event_link');
                                            echo "<a href='{$eventLink}' > View Google Calendar Event </a>";
                                        } ?>
                                    <?php } ?>
                                </div>

                                <div class="ffs_details_info with-table">
                                    <table class="table ffs_reschedule_table">
                                        <?php
                                        if ( is_array($booking['reschedule_data']) && count($booking['reschedule_data']) > 0) {
                                            foreach ($booking['reschedule_data'] as $entry): ?>
                                                <tr class="separator-td">
                                                    <td colspan="2"></td>
                                                </tr>
                                                <tr>
                                                    <th><?php _e('Rescheduled on', FF_BOOKING_SLUG); ?></th>
                                                    <td>
                                                        <?php echo \FF_Booking\Booking\BookingHelper::formatDate($entry->updated_at); ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php _e('Rescheduled By', FF_BOOKING_SLUG); ?></th>
                                                    <td>
                                                        <?php echo ucfirst($entry->action_by); ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php _e('Reason', FF_BOOKING_SLUG); ?></th>
                                                    <td>
                                                        <?php echo trim($entry->reason); ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php _e('Previous Booking', FF_BOOKING_SLUG); ?></th>
                                                    <td><?php echo $entry->previous_booking; ?></td>
                                                </tr>

                                            <?php
                                            endforeach;
                                        } ?>
                                    </table>
                                </div>

                                <div class="ffs_details_info_actions ffs_booking_btns">

                                    <span><?php _e('Change status', FF_BOOKING_SLUG); ?></span>
                                    <select name="" class="ffs_link ffs_booking_status" data-booking_id="<?php echo $booking['id'] ?> ">
                                        <?php foreach ($config['booking_status'] as $key => $status) {
                                            $selected ='';
                                            if($key == $booking['booking_status']){
                                                $selected ='selected';
                                            }
                                            ?>
                                            <option <?php echo $selected?> value="<?php echo $key ?>" >
                                                <?php echo $status?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <a class="ffs_confirm ffs_link" href="<?php echo site_url().'?ff_simple_booking='.$booking['booking_hash'] ?>">
                                        <?php echo $config['reschulde_btn']; ?>
                                    </a>

                                    <div>
                                        <small> <?php _e('Created', FF_BOOKING_SLUG); ?> <?php echo $booking['created_at'] ?> </small>
                                    </div>
                                </div>

                            </div>
                            <div  class="ffs_booking_details ffs_booking_action_confirmation" style="display: none">
                                <h4><?php echo $config['confirm_heading']; ?></h4>
                                <div class="ffs_booking_btns">
                                    <button class="ffs_confirm ffs_link">
                                        <?php echo $config['confirm_btn']; ?>
                                    </button>
                                    <button class="ffs_close ffs_link">
                                        <?php echo $config['close']; ?>
                                    </button>
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
        <?php elseif($config['activeTab'] == 'google_calendar' ): ?>
        <div class="ffsb_tab_body">
            <h4><?php _e('Google Calendar Settings',FF_BOOKING_SLUG)?></h4>
            <form>

                <?php if( !\FluentForm\Framework\Helpers\ArrayHelper::isTrue($config,'gCalendarData.status')) {?>
                <div class="gcalendar_code">
                    <small><a target="_blank" href="<?php echo  \FluentForm\Framework\Helpers\ArrayHelper::get($config,'gCalendarData.authUrl')?>"><?php _e('Get Access Code',FF_BOOKING_SLUG) ?></a></small>
                    <label class="ffsb_label" for="input">Access Code</label>
                    <input class="ffsb_input" type="text" id="ffsb_google_code">
                    <div class="ffs_booking_btns">
                        <a class="ffs_link ffsb_gverify "> <?php _e('Verify', FF_BOOKING_SLUG) ?></a>
                    </div>
                </div>
                <?php } else{ ?>

                    <?php _e('Google Calendar Connected', FF_BOOKING_SLUG) ?>
                    <div class="ffs_booking_btns">
                        <a class="ffs_link ffsb_gverify_diconnect "> <?php _e('Disconnect Google', FF_BOOKING_SLUG) ?></a>
                    </div>
                <?php }; ?>
                <div class="gcalendar_response">
                </div>

            </form>

        </div>
        <?php endif; ?>


    </div>
</div>
