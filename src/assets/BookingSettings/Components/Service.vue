<template>
    <div v-loading="loading" class="ff_booking_settings_container">

        <div>
            <el-row class="setting_header">
                <el-col :md="18">
                    <h2>Service List</h2>
                </el-col>
                <el-col :md="6">
                    <div class="pull-right">
                        <el-button @click="showAddItem()" type="primary" size="small">Add New</el-button>
                    </div>
                </el-col>
            </el-row>

            <el-table :data="items" stripe>
                <el-table-column width="100" label="ID" prop="id"/>
                <el-table-column label="Title" prop="title"/>
                <el-table-column label="Duration" prop="duration"/>
                <el-table-column label="Range">
                    <template slot-scope="scope">
                        <span v-if="scope.row.range_type='days'">
                            {{ scope.row.allowed_future_days }}
                        </span>
                        <span v-else>
                            {{ formatDate(scope.row.allowed_future_date_range[0], 'D MMM, YYYY') }} - {{ formatDate(scope.row.allowed_future_date_range[1], 'D MMM, YYYY') }}
                        </span>

                    </template>
                </el-table-column>

                <el-table-column label="Range">
                    <template slot-scope="scope">
                        {{ scope.row.capacity_type|ucFirst }} {{ booking_types[scope.row.booking_type] }}
                    </template>
                </el-table-column>

                <el-table-column width="140" label="Actions">
                    <template slot-scope="scope">
                        <el-button @click="editItem(scope.row)" type="info" size="mini" icon="el-icon-edit"></el-button>
                        <remove size="mini" icon="el-icon-delete" @on-confirm="deleteItem(scope.row)"></remove>
                    </template>
                </el-table-column>
            </el-table>
            <div style="margin-top: 20px" class="ff_pagination pull-right">
                <pagination :pagination="pagination" @fetch="getItems"/>
            </div>

        </div>
        <el-dialog
                top="40px"
                :title="(editing_item.id) ? 'Edit Service' : 'Add a new Service'"
                :visible.sync="show_modal"
                width="60%">
            <div v-if="show_modal" class="ff_booking_form">
                <el-alert
                        title="Please set a Provider for Date & Schedule"
                        type="info"
                        show-icon>
                </el-alert>
                <el-form :data="editing_item" label-position="top">

                    <el-tabs v-model="activeTab">
                        <el-tab-pane label="Service Details" name="details"></el-tab-pane>
                        <el-tab-pane label="Settings" name="settings"></el-tab-pane>
                        <el-tab-pane label="Required Fields" name="fields"></el-tab-pane>
                        <el-tab-pane label="Notifications" name="notifications"></el-tab-pane>
                        <el-tab-pane label="Advanced" name="advanced"></el-tab-pane>
                    </el-tabs>

                    <div v-if="activeTab == 'details'">
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


                        <!-- in_person location -->
                        <el-form-item label="Address" v-show="editing_item.service_type =='in_person' ">
                            <el-input type="text" v-model="editing_item.in_person_location" placeholder="Address"/>
                            <p>Set Adress of the Meeting </p>
                        </el-form-item>
                        <!-- Description -->
                        <el-form-item label="Description">
                            <el-input type="textarea" v-model="editing_item.description" placeholder="Service Title"/>
                            <p>Description of the Service</p>
                        </el-form-item>


                    </div>
                    <div v-else-if="activeTab == 'settings'">

                        <!-- Date range -->
                        <el-row :gutter="30">
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
                        <!-- Booking Type -->
                        <el-row :gutter="30">

                            <el-col :span="12">
                                <el-form-item label="Booking Type">

                                    <el-select placeholer="Select Type" v-model="editing_item.booking_type">
                                        <el-option v-for="(type,value) in booking_types" :key="value" :label="type"
                                                   :value="value"></el-option>
                                    </el-select>
                                    <p> Slot Type </p>
                                </el-form-item>
                            </el-col>

                            <el-col :span="12" v-if="editing_item.booking_type =='time_slot'">
                                <el-form-item label="Duration">
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
                            </el-col>
                            <el-col v-else :span="12">
                                <div> Full Day will be counted as a slot</div>
                            </el-col>

                        </el-row>


                        <el-collapse v-model="collapseItem" accordion>
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
                                        <el-form-item label="Before">
                                            <el-time-picker
                                                    v-model="editing_item.gap_time_before"
                                                    value-format="HH:mm"
                                                    format="HH:mm"
                                                    :picker-options="{
                                             selectableRange:'00:05:00 - 11:55:00',
                                                 format: 'HH:mm',
                                             }"
                                                    placeholder="Select HH:MM">
                                            </el-time-picker>
                                            <p> Before Slot Start Gap </p>

                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="12">
                                        <el-form-item label="After">
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
                            <el-collapse-item title="Cancelation" name="cancelation">
                                <el-form-item label="Allow user Cancel">
                                    <el-switch active-value="yes" inactive-value="no"
                                               v-model="editing_item.allow_user_cancel"></el-switch>
                                </el-form-item>
                                <el-form-item label="Allow user ReSchedule">
                                    <el-switch active-value="yes" inactive-value="no"
                                               v-model="editing_item.allow_user_reschedule"></el-switch>
                                </el-form-item>
                                <el-form-item label="Policy">
                                    <el-input type="textarea" v-model="editing_item.policy"></el-input>
                                    <p>This will be added to all email </p>
                                </el-form-item>
                            </el-collapse-item>
                        </el-collapse>

                    </div>
                    <div v-else-if="activeTab == 'fields'">
                        <!--Required Fields-->

                        <el-form-item label="Set these fields as required">
                            <el-checkbox-group v-model="editing_item.required_fields">
                                <el-checkbox label="Name"></el-checkbox>
                                <el-checkbox label="Email"></el-checkbox>
                                <el-checkbox label="Phone"></el-checkbox>
                                <el-checkbox label="Details"></el-checkbox>
                            </el-checkbox-group>
                        </el-form-item>

                    </div>

                    <div v-else-if="activeTab == 'notifications'">
                        <Notifications @update-notifications="updateNotifications"
                                       :notifications="editing_item.notifications"></Notifications>
                    </div>
                    <div v-else-if="activeTab == 'advanced'">
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

                            <!--Status on/off-->
                            <el-col :span="12">
                                <el-form-item label="Status">
                                    <el-radio-group v-model="editing_item.status">
                                        <el-radio label="active">Active</el-radio>
                                        <el-radio label="inactive">Inactive</el-radio>
                                    </el-radio-group>
                                    <p>Status</p>
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
                    </div>

                </el-form>
                <div v-if="errors">
                    <ul style="color: red;">
                        <li v-for="error in errors">{{ Object.values(error).join(', ') }}</li>
                    </ul>
                </div>
            </div>
            <span slot="footer" class="dialog-footer">
          <el-button type="primary" v-loading="saving" @click="saveItem()">Save</el-button>
      </span>
        </el-dialog>
    </div>
</template>

<script type="text/babel">
    import Pagination from './inc/_Pagination';
    import Remove from "./inc/confirmRemove";
    import Notifications from "./Notifications";
    import DelayCounter from "./inc/delayCounter";

    export default {
        name: 'Service',
        components: {
            Notifications,
            Pagination,
            Remove,
            DelayCounter
        },
        data() {
            return {
                loading: false,
                saving: false,
                editing_item: {},
                activeTab: 'details',
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
                pagination: {
                    current_page: 1,
                    total: 0,
                    per_page: 10
                },
                show_modal: false,
                available_forms: {},
                errors: false
            }
        },
        methods: {
            getItems() {
                this.loading = true;
                let data = {
                    action: 'handle_booking_ajax_endpoint',
                    route: 'get_services',
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page
                };
                this.$get(data)
                    .then(response => {
                        this.items = response.data.service.data;
                        this.pagination.total = response.data.service.total;
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
            showAddItem() {
                this.editing_item = {
                    title: '',
                    service_type: 'in_person',
                    booking_type: 'time_slot',
                    capacity_type: 'single',
                    range_type: 'days',
                    slot_capacity: '1',
                    max_bookings: '30',
                    default_booking_status: 'booked',
                    calc_value: '',
                    required_fields: [],
                    show_end_time: 'show',
                    show_booked_time: 'hide',
                    duration: '01:00',
                    gap_time_before: '',
                    gap_time_after: '00:30',
                    disable_booking_before: '1 Day',
                    allowed_future_days: '7 Day',
                    allowed_future_date_range: '',
                    notifications: [],
                    allowed_form_ids: [],
                    status: 'active',

                }
                this.show_modal = true;
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
                        this.getItems();
                        this.show_modal = false;
                        this.editing_item = {};
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
                        this.errors = error.responseJSON.errors;
                    })
                    .always(() => {
                        this.saving = false;
                    });

            },
            editItem(item) {
                const editing_item = JSON.parse(JSON.stringify(item));
                this.$set(this, 'editing_item', editing_item);

                if (!this.editing_item.range_type) {
                    this.editing_item.range_type = 'days';
                }
                if (!this.editing_item.allowed_future_days) {
                    this.editing_item.allowed_future_days = '7 Days';
                }
                if (!this.editing_item.allowed_future_date_range) {
                    this.editing_item.allowed_future_date_range = '';
                }
                if (!this.editing_item.required_fields) {
                    this.$set(this.editing_item, 'required_fields', []);
                }


                this.$nextTick(() => {
                    this.show_modal = true;
                });

            },
            updateNotifications(data) {
                this.editing_item.notifications = data;
            },
            deleteItem(service) {
                this.loading = true;
                this.$post({
                    action: 'handle_booking_ajax_endpoint',
                    route: 'delete_service',
                    service_id: service.id
                })
                    .then(response => {
                        this.getItems();
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
                    .always(() => {
                        this.loading = false;
                    });
            }
        },
        computed: {},
        mounted() {
            this.getItems();
        }
    }
</script>
<style>
    .el-dialog__body {
        padding: 10px 20px;
    }
</style>


