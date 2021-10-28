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
                    <el-form-item>
                        <template slot="label">
                            Status
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Status</h3>
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

                    <el-form-item label="Time Zone">
                        <el-select v-model="settings_data.time_zone" filterable placeholder="Select">
                            <el-option
                                    v-for="(timezone) in general_settings.time_zones"
                                    :key="timezone"
                                    :label="timezone"
                                    :value="timezone">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="Time Format">
                        <el-select v-model="settings_data.time_format" filterable placeholder="Select">
                            <el-option label="12 Hour" value="12"></el-option>
                            <el-option label="24 Hour" value="24"></el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item label="Week Start">
                        <el-select v-model="settings_data.week_start" filterable placeholder="Select">
                            <el-option
                                    v-for="(day) in weekdays"
                                    :key="day"
                                    :label="day"
                                    :value="day">
                            </el-option>
                        </el-select>
                    </el-form-item>

                </div>
                <div v-else-if="current_page == 'bookings'">


                    <el-form-item label="Allow Provider To Confirm">
                        <el-switch active-color="#13ce66" v-model="settings_data.allow_provider_confirm"></el-switch>
                    </el-form-item>
                    <el-form-item label="Allow Provider To Cancel">
                        <el-switch active-color="#13ce66" v-model="settings_data.allow_provider_canncel"></el-switch>
                    </el-form-item>
                    <el-form-item label="Allow User To Cancel">
                        <el-switch active-color="#13ce66" v-model="settings_data.allow_user_cancel"></el-switch>
                    </el-form-item>
                    <el-form-item label="Allow User To Set Pending">
                        <el-switch active-color="#13ce66" v-model="settings_data.allow_user_pending"></el-switch>
                    </el-form-item>

                    <el-form-item label="Auto Complete">

                        <el-switch active-color="#13ce66" v-model="settings_data.enable_autocomplete"></el-switch>

                    </el-form-item>

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
                    .fail((error) => {
                        console.log(error)
                    });

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
                    });
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
