<template>
    <div class="ff_booking_info">
        <el-dialog
                top="40px"
                :title="'Booking Info'"
                :visible.sync="show_modal"
                :before-close="handleClose"
                width="60%">
            <el-form v-loading="loading" :data="booking_info" label-position="top">


                <el-form-item label="Form Name">
                    {{ booking_info.form_title }} <a :href="booking_info.submission_url">View Submission</a>
                </el-form-item>

                <el-form-item label="Submission ID">
                    View <a :href="booking_info.submission_url">#{{booking_info.entry_id}}</a>
                </el-form-item>

                <el-row>
                    <el-col :span="12">
                        <el-form-item label="Selected Service">

                            <el-select v-model="booking_info.service_id" placeholder="Service">
                                <el-option
                                        v-for="(value,key) in service_list"
                                        :key="key"
                                        :label="value"
                                        :value="key">
                                </el-option>
                            </el-select>

                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="Selected Provider">

                            <el-select v-model="booking_info.provider_id" placeholder="Provider">
                                <el-option
                                        v-for="(value,key) in provider_list"
                                        :key="key"
                                        :label="value"
                                        :value="key">
                                </el-option>
                            </el-select>

                        </el-form-item>
                    </el-col>

                </el-row>

                <el-row>
                    <el-col :span="12">
                        <el-form-item label="Booking Date">
                            <el-date-picker
                                    v-model="booking_info.booking_date"
                                    type="date"
                                    format="dd-MM-yyyy"
                                    value-format="yyyy-MM-dd"
                                    placeholder="Booking Date">
                            </el-date-picker>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="Booking Time">
                            <el-time-picker
                                    v-model="booking_info.booking_time"
                                    value-format="hh:mm A"
                                    format="hh:mm A"
                                    :picker-options="{
                                           format: 'HH:mm',
                                         }"
                                    placeholder="Booking time">
                            </el-time-picker>
                        </el-form-item>
                    </el-col>

                </el-row>
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="Send Update Notification">
                            <el-checkbox v-model="booking_info.send_notification">Send Notification</el-checkbox>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="Booking Status">

                            <el-select @change="changeStatus(props.row.id,props.row.booking_status)"
                                       v-model="booking_info.booking_status" placeholder="Status">
                                <el-option
                                        v-for="(value,key) in booking_status"
                                        :key="key"
                                        :label="value"
                                        :value="key">
                                </el-option>
                            </el-select>

                        </el-form-item>

                    </el-col>
                </el-row>


                <el-row>
                    <el-col :span="24">
                        <el-form-item label="Booking Notes">
                            <el-input type="textarea" v-model="booking_info.notes" placeholder="Notes"/>
                        </el-form-item>
                    </el-col>
                </el-row>


            </el-form>

            <span slot="footer" class="dialog-footer">
                     <el-button type="primary" v-loading="saving" @click="updateInfo()">Update</el-button>
                </span>


        </el-dialog>

    </div>
</template>
<script>
    export default {
        name: 'BookingInfo',
        props: [],
        data() {
            return {
                loading: false,
                saving: false,
                booking_info: {},
                booking_id: this.$route.params.bookingId,
                service_list: [],
                provider_list: [],
                booking_status: window.ff_booking_settings.booking_status,
            }
        },
        methods: {
            updateInfo() {
                this.saving = true;
                let data = {
                    id: this.booking_info.id,
                    entry_id: this.booking_info.entry_id,
                    service_id: this.booking_info.service_id,
                    form_id: this.booking_info.form_id,
                    provider_id: this.booking_info.provider_id,
                    booking_date: this.booking_info.booking_date,
                    booking_time: this.booking_info.booking_time,
                    booking_status: this.booking_info.booking_status,
                    send_notification: this.booking_info.send_notification,
                    notes: this.booking_info.notes
                }
                this.$post({
                    booking_info: data,
                    action: 'handle_booking_ajax_endpoint',
                    route: 'update_booking',

                })
                    .then(response => {

                        if (response.success != true) {
                            this.$notify.error({
                                title: 'Error',
                                message: response.data.message,
                                offset: 30
                            });
                            return;
                        }
                        this.$notify.success({
                            title: 'Success',
                            message: response.data.message,
                            offset: 30
                        });
                        this.$router.push({
                            name: 'Bookings',
                        });

                    })
                    .fail(error => {
                        this.$notify.error({
                            title: 'Error',
                            message: error.responseJSON.message,
                            offset: 30
                        });
                    })
                    .always(() => {
                        this.saving = false;

                    })
            },
            changeStatus(id, status) {
                this.$post({
                    action: 'handle_booking_ajax_endpoint',
                    route: 'change_status_booking',
                    booking_id: id,
                    entry_id: id,
                    booking_Status: status
                })
                    .then(response => {
                        this.getBookings();
                        this.$notify.success({
                            title: 'Success',
                            message: response.data.message,
                            offset: 30
                        });
                    })
                    .fail(error => {
                        this.$notify.error(error.responseJSON.message);
                    })
                    .always(() => {
                        this.getBookings();
                    });

            },
            handleError: function (message) {
                this.$notify.error({
                    title: 'Error',
                    message: message,
                    offset: 30
                });
                this.$router.push({
                    name: 'Bookings',
                });

            },
            getBookingInfo() {
                this.loading = true;
                this.$get({
                    booking_id: this.booking_id,
                    action: 'handle_booking_ajax_endpoint',
                    route: 'get_booking_info'
                })
                    .then(response => {
                        if (response.success != true) {
                            this.handleError(response.data.message);
                        }
                        this.booking_info = response.data.booking_info
                        this.service_list = response.data.service_list
                        this.provider_list = response.data.provider_list

                    })
                    .fail(error => {
                        this.handleError(error.responseJSON.message);
                    })
                    .always(() => {
                        this.loading = false;
                    });
            },
            handleClose() {
                this.$route.params.show_modal = false;
                this.$router.push({
                    name: 'Bookings',
                });
            }
        },
        computed: {
            show_modal() {
                if (this.$route.params.bookingId) {
                    return true;
                }
                return false;
            }
        },
        mounted() {
            this.getBookingInfo();

        },

    }
</script>
<style>
    .ff_booking_info a {
        text-decoration: none;
    }
</style>
