<template>
    <div v-loading="loading" class="ff_booking_settings_container">
        <el-form label-position="left" :model="settings" label-width="220px">
            <div class="">

                <el-row class="setting_header">
                    <el-col :md="18">
                        <h2>
                            Bookings List
                        </h2>
                    </el-col>
                    <el-col :md="6" class="action-buttons clearfix mb15">

                    </el-col>
                </el-row>
                <div >
                    <el-form label-width="150px" label-position="left">

                        <el-form-item label="Select Range">
                            <el-form-item>
                                <el-date-picker
                                        v-model="date_range"
                                        type="daterange"
                                        size="small"
                                        :picker-options="pickerOptions"
                                        format="dd MMM, yyyy"
                                        value-format="yyyy-MM-dd"
                                        range-separator="-"
                                        @change="getBookings()"
                                        start-placeholder="Start date"
                                        end-placeholder="End date">
                                </el-date-picker>
                            </el-form-item>

                        </el-form-item>

                    </el-form>


                    <el-table
                            v-if="tableData.length > 0"
                            :data="tableData"
                            border
                            style="width: 100%">

                        <el-table-column
                                prop="service"
                                label="Service"
                                width="140"
                        >
                        </el-table-column>
                        <el-table-column
                                prop="provider"
                                label="Provider"
                                width="140"
                        >
                        </el-table-column>
                        <el-table-column
                                label="Date">
                            <template slot-scope="props">
                                {{ formatDate( props.row.formatDate )}}
                            </template>
                        </el-table-column>
                        <el-table-column
                                label="Time">
                            <template slot-scope="props">
                                {{ formatTime( props.row.booking_time )}}
                            </template>
                        </el-table-column>


                        <el-table-column
                                label="Submission ID"
                        >
                            <template slot-scope="props">
                                <a v-if="props.row.submission_url"
                                   :href="props.row.submission_url">#{{ props.row.entry_id }}</a>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="human_date"
                                label="Created">
                        </el-table-column>
                        <el-table-column
                                label="Status">
                            <template slot-scope="props">
                                <div>
                                    <el-tag class="ff-booking-stat " v-if="props.row.booking_status =='booked'"
                                            type="success">{{ props.row.booking_status }}
                                    </el-tag>
                                    <el-tag class="ff-booking-stat " v-else-if="props.row.booking_status =='pending'"
                                            type="warning">{{ props.row.booking_status }}
                                    </el-tag>
                                    <el-tag class="ff-booking-stat " v-else type="info">{{
                                            props.row.booking_status
                                        }}
                                    </el-tag>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column
                                label="Action"
                                width="200">
                            <template slot-scope="props">
                                <el-select size="small" @change="changeStatus(props.row.id,props.row.booking_status)"
                                           v-model="props.row.booking_status" placeholder="Select">
                                    <el-option
                                            v-for="(value,key) in booking_status"
                                            :key="key"
                                            :label="value"
                                            :value="key">
                                    </el-option>
                                </el-select>

                            </template>
                        </el-table-column>
                        <el-table-column
                        >
                            <template slot-scope="props">
                                <el-button @click="showDetails(props.row.id)" type="primary" icon="el-icon-view"
                                           size="mini"></el-button>
                            </template>

                        </el-table-column>

                    </el-table>
                    <div v-else> No Bookings </div>
                    <div style="margin-top: 20px" class="ff_pagination pull-right">
                        <pagination :pagination="pagination" @fetch="getBookings"/>
                    </div>
                </div>



            </div>
        </el-form>

        <div class="action_right">
        </div>
        <!-- Booking Info -->

            <div v-if="show_modal" class="ff_booking_form">
                <router-view></router-view>
            </div>


    </div>
</template>

<script type="text/babel">
    import Pagination from './inc/_Pagination';

    export default {
        name: 'Bookings',
        props: [],
        components: {
            Pagination,
        },
        data() {
            return {
                logs: [],
                loading: false,
                show_modal: false,
                booking_status: {
                    'booked': 'Confirm Booking',
                    'pending': 'Pending Booking',
                    'canceled': 'Cancel Booking',
                    'declined': 'Decline Booking',
                    'complete': 'Booking Complete',
                    'draft': 'Draft Booking',
                },
                tableData: [],
                settings: {},
                date_range: [],
                pagination: {
                    current_page: 1,
                    total: 0,
                    per_page: 15
                },
                pickerOptions: {
                    disabledDate(time) {
                        return time.getTime() >= Date.now();
                    },
                    shortcuts: [
                        {
                            text: 'Today',
                            onClick(picker) {
                                const start = new Date();
                                picker.$emit('pick', [start, start]);
                            }
                        },
                        {
                            text: 'Next week',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                end.setTime(end.getTime() + 3600 * 1000 * 24 * 7);
                                picker.$emit('pick', [start, end]);
                            }
                        },

                    ]
                },
            }

        },
        watch: {
            $route(newVal, oldVal) {
                this.show_modal = newVal.meta && newVal.meta.show_modal;
            }
        },
        methods: {

            getBookings() {
                this.loading = true;
                let data = {
                    action: 'handle_booking_ajax_endpoint',
                    route: 'get_bookings',
                    date_range: this.date_range,
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page
                };

                this.$get(data)
                    .then(response => {
                        this.tableData = response.data.bookings.data;
                        this.pagination.total = response.data.bookings.total;
                    })
                    .always(() => {
                        this.loading = false;
                    });
            },
            changeStatus(id, status) {
                this.$post({
                    action: 'handle_booking_ajax_endpoint',
                    route: 'change_status_booking',
                    booking_id: id,
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
            setDefaultDate(data) {
                const end = new Date();
                const start = new Date();
                start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                this.date_range = [this.$date(start).format('YYYY-MM-DD'), this.$date(end).format('YYYY-MM-DD')];
            },
            showDetails(id) {
                this.$router.push({
                    name: 'BookingInfo', params: {bookingId: id,show_modal : true},
                });
                this.show_modal = true;
            },


        },
        computed: {},

        mounted() {
            this.setDefaultDate()
            this.getBookings();
            if (this.$route.params.bookingId) {
                this.showDetails(this.$route.params.bookingId)
            }
        },

    }
</script>

<style lang="scss">
  .item_full_width {
    width: 100%;
  }

</style>
