<template>
    <div class="ff_booking_settings_container">
        <div class="ff_booking_navigation">
            <ul>
                <li @click="current_page = 'general'" :class="{ff_active: current_page == 'general'}">General</li>
                <li @click="current_page = 'bookings'" :class="{ff_active: current_page == 'bookings'}">Bookings</li>
            </ul>
        </div>

        <el-form label-position="left" label-width="220px">
            <div class="ff_booking_settings_section">
                <div v-if="current_page == 'general'">
                    <h4>
                        <code>[ff_simple_booking] </code> Add this shortcode to list & manage providers bookings
                    </h4>
                    <el-form-item>
                        <template slot="label">
                            Status
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <p>
                                        Disable Booking
                                    </p>
                                </div>
                                <i class="el-icon-info el-text-info"></i>
                            </el-tooltip>
                        </template>
                        <el-checkbox @change="toggleBookingModule" true-label="1" false-label="0"
                                     v-model="general_settings.is_setup">Enable Booking Module
                        </el-checkbox>
                    </el-form-item>


                </div>
                <div v-else-if="current_page == 'bookings'">


                    <el-form-item label="Allow Provider To ReSchedule">
                        <el-switch active-color="#13ce66" v-model="settings_data.allow_provider_reschedule"></el-switch>
                    </el-form-item>
<!--                    todo auto complete bookings-->


                </div>

            </div>
        </el-form>

        <div class="action_right">
            <el-button @click="saveSettings()" type="primary" size="small">Save Settings</el-button>
        </div>

        <h3 style="color: red" v-if="general_settings.status == 'no'">Booking Module has been disabled currently.</h3>

    </div>
</template>

<script type="text/babel">

    export default {
        name: 'general_payment_settings',
        props: ['settings'],
        components: {},
        data() {
            return {
                general_settings: window.ff_booking_settings,
                settings_data: {
                    time_format: 12,
                    week_start: 'Monday',
                },
                weekdays: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                current_page: 'general'
            }
        },
        methods: {
            saveSettings() {
                this.$post({
                    action: 'handle_booking_ajax_endpoint',
                    settings_data: JSON.stringify(this.settings_data),
                    route: 'save_settings'
                })
                    .then(response => {
                        console.log(response)
                        if (response.success == true) {
                            this.$notify.success({
                                title: 'Success',
                                message: response.data.message,
                                offset: 30
                            });
                        }
                    })
                    .fail((error) => {
                        console.log(error)
                    });

            },
            getSettings() {
                this.$post({
                    action: 'handle_booking_ajax_endpoint',
                    route: 'get_settings'
                })
                    .then(response => {
                        if (response.success == true) {
                            this.settings_data = response.data.settings_data
                            if(!this.settings_data){
                               this.settings_data = {};
                            }
                        }
                    })
                    .fail(error => {
                        this.$notify.error({
                            title: 'Error',
                            message: error.responseJSON.message,
                            offset: 30
                        });
                    })

            },
            toggleBookingModule() {
                this.$post({
                    action: 'handle_booking_ajax_endpoint',
                    status: this.general_settings.is_setup,
                    route: 'toggle_booking'
                })
                    .then(response => {
                        this.$notify.success({
                            title: 'Success',
                            message: response.data.message,
                            offset: 30
                        });
                    })
                    .fail(error => {
                        this.$notify.error({
                            title: 'Error',
                            message: error.responseJSON.message,
                            offset: 30
                        });
                    })
                ;
            },
        },
        mounted() {
            this.getSettings();
        }
    }
</script>

<style lang="scss">
  .item_full_width {
    width: 100%;
  }

</style>
