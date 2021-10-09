<template>
    <div v-loading="loading" class="ff_method_settings">

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
                :append-to-body="true"
                width="60%">
            <div v-if="show_modal" class="ff_booking_form">
                <el-form :data="editing_item" label-position="top">

                    <el-form-item label="Service Title">
                        <el-input type="text" v-model="editing_item.title" placeholder="Service Title"/>
                        <p>Name of the Service</p>
                    </el-form-item>
                    <el-row :gutter="30">
                        <el-col :span="12">
                            <el-form-item label="Booking Type">

                                <el-select placeholer="Select Type" v-model="editing_item.booking_type">
                                    <el-option v-for="(type,value) in booking_types" :key="value" :label="type"
                                               :value="value"></el-option>
                                </el-select>
                                <p> Booking Slot Type </p>
                            </el-form-item>
                        </el-col>

                        <el-col :span="12">
                            <el-form-item label="Time Format">

                                <el-radio-group v-model="editing_item.time_format">
                                    <el-radio label="12">12 Hour</el-radio>
                                    <el-radio label="24">24 Hour</el-radio>
                                </el-radio-group>

                                <p>Slot Time format </p>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row :gutter="30">


                        <el-col :span="12">
                            <el-form-item label="Duration Hour & Minute">

                                <el-time-picker
                                        v-model="editing_item.duration"
                                        value-format="HH:mm"
                                        format="HH:mm"
                                        :picker-options="{
                         selectableRange:'00:05:00 - 12:00:00',
                             format: 'HH:mm',
                         }"
                                        placeholder="Select Hour:Minute">
                                </el-time-picker>
                                <span>HH:mm</span>
                                <p> Booking Slot Duration </p>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Gap Hour & Minute">
                                <el-time-picker
                                        v-model="editing_item.gap_time"
                                        value-format="HH:mm"
                                        format="HH:mm"
                                        :picker-options="{
                         selectableRange:'00:05:00 - 11:55:00',
                             format: 'HH:mm',
                         }"
                                        placeholder="Select Hour:Minute">
                                </el-time-picker>
                                <span>HH:mm</span>
                                <p> Booking Slot Gap </p>


                            </el-form-item>

                        </el-col>

                    </el-row>

                    <el-row :gutter="30">
                        <el-col :span="8">
                            <el-form-item label="Slot Capacity">
                                <el-input-number v-model="editing_item.slot_capacity" :min="1"
                                                 :max="20"></el-input-number>
                                <p>Single Slot Capacity </p>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="Max Booking">
                                <el-input-number v-model="editing_item.max_bookings" :min="1"
                                                 :max="20"></el-input-number>
                                <p>Maximum bookings in a day</p>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="Calculation Value">
                                <el-input-number v-model="editing_item.calc_value" :min="0"
                                                 :max="999"></el-input-number>
                                <p>Set calculation Value for calculation </p>
                            </el-form-item>
                        </el-col>
                    </el-row>


                    <el-row :gutter="30">
                        <el-col :span="8">
                            <el-form-item label="Show End Time Slot">
                                <el-radio-group v-model="editing_item.show_end_time">
                                    <el-radio label="show">Show</el-radio>
                                    <el-radio label="hide">Hide</el-radio>
                                </el-radio-group>
                                <p>Show Booking End Time Slot</p>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="Show Booked Slot">
                                <el-radio-group v-model="editing_item.show_booked_time">
                                    <el-radio label="show">Show</el-radio>
                                    <el-radio label="hide">Hide</el-radio>
                                </el-radio-group>
                                <p>Show Booked Time Slot</p>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="Status">

                                <el-radio-group v-model="editing_item.status">
                                    <el-radio label="active">Active</el-radio>
                                    <el-radio label="inactive">Inactive</el-radio>
                                </el-radio-group>
                                <p>Status</p>
                            </el-form-item>
                        </el-col>

                    </el-row>
                    <el-row>

                        <el-col :span="12">
                            <el-form-item label="Advanced Booking Days">
                                <el-input-number v-model="days_value" :min="1" :max="20"></el-input-number>
                                <span>
                   <el-select placeholer="Select Forms" v-model="days_unit">
                     <el-option v-for="unit in days_data" :key="unit" :label="unit" :value="unit"></el-option>
                  </el-select>
                </span>
                                <p>Allowed advanced days for booking </p>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Booking Status">

                                <el-select placeholer="Default Status" v-model="editing_item.default_booking_status">
                                    <el-option v-for="(name,value) in booking_status" :key="value" :label="name"
                                               :value="value"></el-option>
                                </el-select>
                                <p> Default Booking Status </p>
                            </el-form-item>
                        </el-col>

                        <el-col :span="24">
                            <el-form-item label="Applicable Forms">
                                <el-select placeholer="Select Forms" style="width: 100%;" multiple
                                           v-model="editing_item.allowed_form_ids">
                                    <el-option v-for="(formName, formId) in available_forms" :key="formId"
                                               :label="formName" :value="formId"></el-option>
                                </el-select>
                                <p>Leave blank for applicable for all forms</p>
                            </el-form-item>

                        </el-col>
                    </el-row>


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

    export default {
        name: 'Provider',
        components: {
            Pagination,
            Remove
        },
        data() {
            return {
                loading: false,
                saving: false,
                items: [],
                days_data: [
                    'Day',
                    'Week',
                    'Month',
                    'Year',
                ],
                booking_types: {
                    'time_slot': 'Time Slot',
                    'date_slot': 'Date Slot',
                    'custom_slot': 'Custom Slot',
                },
                booking_status: {
                    'booked': 'Booked',
                    'pending': 'Pending',
                    'canceled': 'Canceled',
                    'rejected': 'Rejected',
                    // 'completed' :'completed',
                },
                days_value: '7',
                days_unit: 'Days',
                pagination: {
                    current_page: 1,
                    total: 0,
                    per_page: 10
                },
                editing_item: {},
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
                    duration: '01:00',
                    gap_time: '00:30',
                    time_format: '12',
                    booking_type: 'time_slot',
                    slot_capacity: '1',
                    max_bookings: '20',
                    calc_value: '',
                    status: 'active',
                    default_booking_status: 'booked',
                    show_end_time: 'show',
                    show_booked_time: 'hide',
                    allowed_form_ids: [],
                    allowed_future_days: '7 days',
                }
                this.show_modal = true;
            },
            saveItem() {
                this.saving = true;
                this.errors = false;
                this.editing_item.allowed_future_days = this.formatFutureDays;
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
                        this.$notify.error(error.responseJSON.message);
                        this.errors = error.responseJSON.errors;
                    })
                    .always(() => {
                        this.saving = false;
                    });

            },
            editItem(item) {
                const editing_item = JSON.parse(JSON.stringify(item));
                this.$set(this, 'editing_item', editing_item);

                if (!this.editing_item.allowed_future_days) {
                    this.editing_item.allowed_future_days = '7 Days';
                }
                this.days_value = this.editing_item.allowed_future_days.split(" ")[0];
                this.days_unit = this.editing_item.allowed_future_days.split(" ")[1];

                this.$nextTick(() => {
                    this.show_modal = true;
                });

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
        computed: {
            formatFutureDays: function () {
                if (!this.days_unit || !this.days_value) {
                    return;
                }
                return this.days_value + ' ' + this.days_unit;
            },
        },
        mounted() {
            this.getItems();
        }
    }
</script>


