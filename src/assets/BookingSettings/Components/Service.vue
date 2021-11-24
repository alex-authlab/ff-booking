<template>
    <div v-loading="loading" class="ff_booking_settings_container">

        <div class="ffs_settings_holder">
            <el-row class="setting_header">
                <el-col :md="18">
                    <h2>
                        <span v-if="isEditing">{{ $t('Edit Service') }}}</span>
                        <span v-else="isEditing">{{ $t('New Service') }} </span>
                    </h2>
                </el-col>
                <el-col :md="6">
                    <div class="pull-right">

                        <el-button type="primary" size="medium" v-loading="saving" @click="saveItem()">
                            {{ $t('Save') }}
                        </el-button>
                    </div>
                </el-col>
            </el-row>
            <el-form :data="editing_item" label-position="top">

                <div v-if="errors">
                    <ul style="color: red;">
                        <li v-for="error in errors">{{ Object.values(error).join(', ') }}</li>
                    </ul>
                </div>
                <el-collapse class="ffs_collapse_div" v-model="activeName">
                    <el-collapse-item title="Service Details" name="info">

                        <div>
                            <el-row :gutter="30">
                                <el-col :span="9">
                                    <!--details-->
                                    <el-form-item label="Service Title">
                                        <el-input type="text" v-model="editing_item.title" placeholder="Service Title"/>
                                        <p>Name of the Service</p>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="9">
                                    <!--place-->
                                    <el-form-item label="Serivce Type">
                                        <el-select placeholer="Select " v-model="editing_item.service_type">
                                            <el-option v-for="(name,value) in service_types" :key="value" :label="name"
                                                       :value="value"></el-option>
                                        </el-select>
                                        <p>Select Where the service will be provided </p>
                                    </el-form-item>
                                </el-col>

                                <el-col :span="4">
                                    <!-- Color -->
                                    <el-form-item label="Color">
                                        <el-color-picker
                                                v-model="editing_item.color"
                                                :predefine="predefineColors">
                                        </el-color-picker>
                                        <p>Service Color Mark </p>
                                    </el-form-item>

                                </el-col>

                            </el-row>
                            <el-form-item label="Active">
                                <el-switch
                                        v-model="editing_item.status"
                                        active-value='active'
                                        inactive-value='inactive'
                                        active-color="#13ce66">
                                </el-switch>
                            </el-form-item>

                        </div>
                    </el-collapse-item>
                    <el-collapse-item title="Date & Time Settings " name="date_time">
                        <div>
                            <!-- Date range -->
                            <el-row>
                                <el-col :span="12">
                                    <el-form-item label="Range Type">

                                        <el-radio-group v-model="editing_item.range_type">
                                            <el-radio label="days">Days</el-radio>
                                            <el-radio label="date_range">Date Range</el-radio>
                                        </el-radio-group>

                                        <p>Allowed Booking Range in Days count or Date range</p>
                                    </el-form-item>
                                </el-col>


                                <el-col :span="12" v-if="editing_item.range_type == 'days'">
                                    <el-form-item label="Allowed Range">
                                        <delay-counter v-model="editing_item.allowed_future_days"></delay-counter>
                                        <p>People can book advance within this range </p>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="12" v-else-if="editing_item.range_type == 'date_range'">
                                    <el-form-item label="Date Range">
                                        <el-date-picker
                                                v-model="editing_item.allowed_future_date_range"
                                                type="daterange"
                                                size="small"
                                                :picker-options="pickerOptions"
                                                format="dd MMM, yyyy"
                                                value-format="yyyy-MM-dd"
                                                range-separator="-"
                                                start-placeholder="Start date"
                                                end-placeholder="End date">
                                        </el-date-picker>
                                    </el-form-item>
                                </el-col>

                            </el-row>
                            <el-row>
                                <el-col :span="12">
                                    <el-form-item label="Booking Type">

                                        <el-select placeholer="Select Type" v-model="editing_item.booking_type">
                                            <el-option v-for="(type,value) in booking_types" :key="value" :label="type"
                                                       :value="value"></el-option>
                                        </el-select>
                                        <p> Slot Type </p>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="12">
                                    <el-form-item v-if="editing_item.booking_type =='time_slot'" label="Duration">
                                        <el-time-picker
                                                v-model="editing_item.duration"
                                                value-format="HH:mm"
                                                format="HH:mm"
                                                step="00:30:00"
                                                :picker-options="{
                                             selectableRange:'00:05:00 - 12:00:00',
                                                 format: 'HH:mm',
                                             }"
                                                placeholder="Select Hour:Minute">
                                        </el-time-picker>
                                        <p> Slot Duration in Hour and Minute </p>
                                    </el-form-item>
                                    <div v-else-if="editing_item.booking_type =='date_slot'"> Full Day will be counted
                                        as a slot
                                    </div>
                                </el-col>
                            </el-row>

                        </div>
                    </el-collapse-item>
                    <el-collapse-item title="Notification" name="notification">
                        <Notifications @update-notifications="updateNotifications"
                                       :notifications="editing_item.notifications"></Notifications>
                    </el-collapse-item>
                    <el-collapse-item title="Capacity" name="capacity">

                        <!-- Capacity -->
                        <el-row :gutter="30">
                            <el-col :span="12">
                                <el-form-item label="Capacity Type">
                                    <el-radio-group v-model="editing_item.capacity_type">
                                        <el-radio label="single">Single</el-radio>
                                        <el-radio label="multiple">Multiple</el-radio>
                                    </el-radio-group>


                                    <p> Booking Capacity Single or multiple
                                        {{ editing_item.capacity_type }} </p>
                                </el-form-item>
                            </el-col>
                            <el-col :span="12">
                                <el-form-item label="Calculation Value">
                                    <el-input-number v-model="editing_item.calc_value" :min="0"
                                                     :max="999"></el-input-number>
                                    <p>Set calculation Value for calculation </p>
                                </el-form-item>
                            </el-col>


                        </el-row>
                        <el-row v-show="editing_item.capacity_type == 'multiple'">
                            <el-col :span="12">
                                <el-form-item label="Slot Capacity">
                                    <el-input-number v-model="editing_item.slot_capacity" :min="1"
                                                     :max="20"></el-input-number>
                                    <p>Single Slot Capacity </p>
                                </el-form-item>
                            </el-col>
                            <el-col :span="12">
                                <el-form-item label="Show Remaining Slot">
                                    <el-radio-group v-model="editing_item.show_remaining_slot">
                                        <el-radio label="show">Show</el-radio>
                                        <el-radio label="hide">Hide</el-radio>
                                    </el-radio-group>
                                    <p>Show Remaining Time Slot in a Mulitple Booking Slot</p>
                                </el-form-item>
                            </el-col>

                        </el-row>

                    </el-collapse-item>
                    <el-collapse-item title="Buffer Time" name="time">
                        <el-row :gutter="30">
                            <el-col :span="12">

                            </el-col>
                            <el-col :span="12">
                                <el-form-item label="Slot Gap Time">
                                    <el-time-picker
                                            v-model="editing_item.gap_time_after"
                                            value-format="HH:mm"
                                            format="HH:mm"
                                            :picker-options="{
                                             selectableRange:'00:05:00 - 11:55:00',
                                                 format: 'HH:mm',
                                             }"
                                            placeholder="Select HH:MM">
                                    </el-time-picker>
                                    <p> After Slot Start Gap</p>
                                </el-form-item>
                            </el-col>
                        </el-row>

                    </el-collapse-item>
                    <el-collapse-item title="Advanced" name="advanced">
                        <!--Applicable Forms-->
                        <el-form-item label="Applicable Forms">
                            <el-select placeholer="Select Forms" style="width: 100%;" multiple
                                       v-model="editing_item.allowed_form_ids">
                                <el-option v-for="(formName, formId) in available_forms" :key="formId"
                                           :label="formName" :value="formId"></el-option>
                            </el-select>
                            <p>Leave blank to apply for all forms</p>
                        </el-form-item>
                        <!--Default Status-->

                        <el-row :gutter="30">
                            <!--Show End Time-->
                            <el-col :span="12">
                                <el-form-item label="Show End Time Slot">
                                    <el-radio-group v-model="editing_item.show_end_time">
                                        <el-radio label="show">Show</el-radio>
                                        <el-radio label="hide">Hide</el-radio>
                                    </el-radio-group>
                                    <p>Show Booking End Time Slot</p>
                                </el-form-item>
                            </el-col>
                            <!--Show Booked Time-->
                            <el-col :span="12">
                                <el-form-item label="Show Booked Slot">
                                    <el-radio-group v-model="editing_item.show_booked_time">
                                        <el-radio label="show">Show</el-radio>
                                        <el-radio label="hide">Hide</el-radio>
                                    </el-radio-group>
                                    <p>Show Booked Time Slot</p>
                                </el-form-item>
                            </el-col>

                            <!--Default Booking Status-->
                            <el-col :span="12">
                                <el-form-item label="Booking Status">
                                    <el-select placeholer="Default Status"
                                               v-model="editing_item.default_booking_status">
                                        <el-option v-for="(name,value) in booking_status" :key="value"
                                                   :label="name"
                                                   :value="value"></el-option>
                                    </el-select>
                                    <p> Default Booking Status </p>
                                </el-form-item>
                            </el-col>
                            <!--Turn off booking before-->
                            <el-col :span="12">
                                <el-form-item label="Turn off Booking Before">
                                    <delay-counter v-model="editing_item.disable_booking_before"></delay-counter>
                                    <p>People can book advance within this range </p>
                                </el-form-item>
                            </el-col>
                            <!--Max Booking in a day-->
                            <el-col :span="12">
                                <el-form-item label="Max Booking">
                                    <el-input-number v-model="editing_item.max_bookings" :min="1"
                                                     :max="99"></el-input-number>
                                    <p>Max Allowed Bookings in a day</p>
                                </el-form-item>
                            </el-col>

                        </el-row>
                        <el-form-item label="Show Booking Info">
                            <el-radio-group v-model="editing_item.append_info">
                                <el-radio label="yes">Active</el-radio>
                                <el-radio label="no">Inactive</el-radio>
                            </el-radio-group>
                            <p>Append Booking Info after submission </p>
                        </el-form-item>
                    </el-collapse-item>
                    <el-collapse-item title="Cancelation" name="cancelation">
                        <el-form-item label="Allow user Cancel">

                            <el-switch active-value="yes" inactive-value="no"
                                       v-model="editing_item.allow_user_cancel"></el-switch>
                        </el-form-item>
                        <el-form-item label="Allow user ReSchedule">

                            <el-switch active-value="yes" inactive-value="no"
                                       v-model="editing_item.allow_user_reschedule"></el-switch>
                        </el-form-item>
                        <p>Links will be added in email to cancel or ReSchedule in Booking Info Page </p>

                        <el-form-item label="Policy">
                            <el-input type="textarea" v-model="editing_item.policy"></el-input>
                            <p>This will be added to all email </p>
                        </el-form-item>

                    </el-collapse-item>
                </el-collapse>

            </el-form>

        </div>
    </div>
</template>

<script type="text/babel">
    import Remove from "./inc/confirmRemove";
    import Notifications from "./Notifications";
    import DelayCounter from "./inc/delayCounter";

    export default {
        name: 'Service',
        props: ['service_id'],
        components: {
            Notifications,
            Remove,
            DelayCounter
        },
        data() {
            return {
                loading: false,
                saving: false,
                activeName: 'info',
                editing_item: {},
                items: [],
                service_types: {
                    in_person: 'In Person',
                    // google_meet: 'Google Meet',
                    // @todo connect with external calendar provider
                },
                predefineColors: [
                    '#ff4500',
                    '#ff8c00',
                    '#ffd700',
                    '#90ee90',
                    '#00ced1',
                ],
                booking_types: {
                    'time_slot': 'Time Slot',
                    'date_slot': 'Date Slot',
                    //custom date time slot
                },
                collapseItem: '',
                pickerOptions: {
                    disabledDate(time) {
                        return time.getTime() <= Date.now();
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
                            text: 'Yesterday',
                            onClick(picker) {
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 1);
                                picker.$emit('pick', [start, start]);
                            }
                        },
                        {
                            text: 'Last week',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                                picker.$emit('pick', [start, end]);
                            }
                        },
                        {
                            text: 'Last 15 Days',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 15);
                                picker.$emit('pick', [start, end]);
                            }
                        }, {
                            text: 'Last month',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                                picker.$emit('pick', [start, end]);
                            }
                        }
                    ]
                },
                booking_status: {
                    'booked': 'Booked',
                    'pending': 'Pending',
                    'canceled': 'Canceled',
                    'rejected': 'Rejected',
                    // 'completed' :'completed',
                },

                show_modal: false,
                available_forms: {},
                errors: false
            }
        },
        methods: {
            getItem() {
                this.loading = true;
                let data = {
                    action: 'handle_booking_ajax_endpoint',
                    route: 'get_service',
                    service_id: (this.service_id) ? this.service_id : false,
                };
                this.$get(data)
                    .then(response => {
                        this.editing_item = response.data.service;
                        this.available_forms = response.data.available_forms;
                    })
                    .fail(error => {
                        this.$notify.error({
                            title: 'Error',
                            message: error.responseJSON.message,
                            offset: 30
                        });
                    })
                    .always(() => {
                        this.loading = false;
                    })

            },

            saveItem() {
                this.saving = true;
                this.errors = false;
                let data = {
                    action: 'handle_booking_ajax_endpoint',
                    route: 'save_service',
                    service: this.editing_item
                };
                this.$post(data)
                    .then(response => {
                        this.getItem();
                        this.show_modal = false;
                        this.editing_item = {};
                        this.$notify.success({
                            title: 'Success',
                            message: response.data.message,
                            offset: 30
                        });
                        this.$router.push({
                            name: 'services',
                        });

                    })
                    .fail(error => {
                        this.$notify.error({
                            title: 'Error',
                            message: error.responseJSON.message,
                            offset: 30
                        });
                        this.errors = error.responseJSON.errors;
                    })
                    .always(() => {
                        this.saving = false;
                    });

            },
            updateNotifications(data) {

                this.$set(this.editing_item, 'notifications', data);
            },
        },
        computed: {
            isEditing() {
                return (this.service_id) ? true : false;
            }
        },
        mounted() {
            this.getItem();

        }
    }
</script>
<style>
    .el-dialog__body {
        padding: 10px 20px;
    }
</style>


