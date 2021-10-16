<template>
    <div v-loading="loading" class="ff_booking_settings_container">
        <div class="ff_pre_settings_wrapper" v-if="!coupon_status">
            <h2>Booking Provider</h2>
        </div>
        <div v-else>
            <el-row class="setting_header">
                <el-col :md="18">
                    <h2>Providers List</h2>
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
                <el-table-column label="User" prop="user_assigned">
                    <template slot-scope="scope">
                        {{ getUser(scope.row.assigned_user) }}
                    </template>
                </el-table-column>

                <el-table-column width="140" label="Services">
                    <template slot-scope="scope">
                        {{ getServices(scope.row.assigned_services) }}
                    </template>
                </el-table-column>
                <el-table-column width="140" label="Actions">
                    <template slot-scope="scope">
                        <el-button @click="editItem(scope.row)" type="info" size="mini" icon="el-icon-edit"></el-button>
                        <el-button @click="deleteItem(scope.row)" type="danger" size="mini"
                                   icon="el-icon-delete"></el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="margin-top: 20px" class="ff_pagination pull-right">
                <pagination :pagination="pagination" @fetch="getItems"/>
            </div>

        </div>
        <el-dialog
                top="40px"
                :title="(editing_item.id) ? 'Edit Provider' : 'Add a new Provider'"
                :visible.sync="show_modal"
                width="60%">
            <div v-if="show_modal" class="ff_booking_form">
                <el-form :data="editing_item" label-position="top">
                    <el-form-item label="Provider Title">
                        <el-input type="text" v-model="editing_item.title" placeholder="Provider Title"/>
                        <p>Name of the provider</p>
                    </el-form-item>

                    <el-row :gutter="30">
                        <el-col :span="12">
                            <el-form-item label="Assigned User">
                                <el-select placeholer="Select User" style="width: 100%;"
                                           v-model="editing_item.assigned_user">
                                    <el-option v-for="(user,userId) in users" :key="userId" :label="user"
                                               :value="userId"></el-option>
                                </el-select>
                                <p>Assign user to this provider</p>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Assigned Services">
                                <el-select placeholer="Select Service" style="width: 100%;" multiple
                                           v-model="editing_item.assigned_services">
                                    <el-option v-for="(serviceName, serviceId) in services" :key="serviceId"
                                               :label="serviceName"
                                               :value="serviceId"></el-option>
                                </el-select>
                                <p>Add services under this provider</p>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Weekly Off Days">
                                <el-checkbox-group v-model="editing_item.weekend_days">

                                    <el-checkbox v-for="weekday in weekdays" :key="weekday"
                                                 :label="weekday"></el-checkbox>

                                </el-checkbox-group>
                                <p>Select Weekly off Days</p>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Holidays">
                                <el-date-picker
                                        v-model="editing_item.holiday_dates"
                                        type="dates"
                                        format="dd-MM-yyyy"
                                        value-format="yyyy-MM-dd"
                                        placeholder="Pick a Holidays">
                                </el-date-picker>
                                <p>Select Holiday Dates</p>
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <el-row :gutter="30">
                        <el-col :span="12">
                            <el-form-item label="Start Time">
                                <el-time-picker
                                        v-model="editing_item.start_time"
                                        value-format="hh:mm A"
                                        format="hh:mm A"
                                        :picker-options="{
                           format: 'HH:mm',
                         }"
                                        placeholder="Select time">
                                </el-time-picker>
                                <p>Select Start Time</p>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="End Time">
                                <el-time-picker
                                        v-model="editing_item.end_time"
                                        value-format="hh:mm A"
                                        format="hh:mm A"
                                        :picker-options="{
                           format: 'HH:mm',
                         }"
                                        placeholder="Select time">
                                </el-time-picker>
                                <p>Select End Time</p>
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <el-form-item label="Applicable Forms">
                        <el-select placeholer="Select Forms" style="width: 100%;" multiple
                                   v-model="editing_item.allowed_form_ids">
                            <el-option v-for="(formName, formId) in available_forms" :key="formId" :label="formName"
                                       :value="formId"></el-option>
                        </el-select>
                        <p>Leave blank for applicable for all forms</p>
                    </el-form-item>

                    <el-form-item label="Status">
                        <el-radio-group v-model="editing_item.status">
                            <el-radio label="active">Active</el-radio>
                            <el-radio label="inactive">Inactive</el-radio>
                        </el-radio-group>
                    </el-form-item>
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
    import Pagination from './inc/_Pagination'

    export default {
        name: 'Provider',
        components: {
            Pagination
        },
        data() {
            return {
                loading: false,
                saving: false,
                items: [],
                coupon_status: true,
                pagination: {
                    current_page: 1,
                    total: 0,
                    per_page: 10
                },
                editing_item: {},
                show_modal: false,
                available_forms: {},
                users: {},
                services: {},
                weekdays: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                errors: false
            }
        },
        methods: {
            getItems() {
                this.loading = true;
                let data = {
                    action: 'handle_booking_ajax_endpoint',
                    route: 'get_providers',
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page
                };
                this.$get(data).then(response => {
                    this.items = response.data.providers.data;
                    this.users = response.data.users;
                    this.services = response.data.services;
                    this.pagination.total = response.data.providers.total;
                    this.available_forms = response.data.available_forms
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
                    assigned_user: '',
                    assigned_services: [],
                    weekend_days: [],
                    holiday_dates: [],
                    status: 'active',
                    allowed_form_ids: [],
                    start_time: '09:00 AM',
                    end_time: '04:00 PM',
                }
                if (this.users) {
                    this.editing_item.assigned_user = Object.keys(this.users)[0];
                }
                if (this.services) {
                    this.editing_item.assigned_services = [Object.keys(this.services)[0]];
                }

                this.show_modal = true;
            },
            saveItem() {
                this.saving = true;
                this.errors = false;
                let data = {
                    action: 'handle_booking_ajax_endpoint',
                    route: 'save_providers',
                    provider: this.editing_item,
                };
                this.$post(data).then(response => {
                    this.editing_item = {};
                    this.show_modal = false;
                    this.saving = false;
                    this.getItems();
                    this.$notify.success({
                        title: 'Success',
                        message: response.data.message,
                        offset: 30
                    });
                })
                    .fail(error => {
                        console.log(error)
                        this.errors = error.responseJSON.errors;
                    })
                    .always(() => {
                        this.saving = false;
                    })
            },
            editItem(provider) {
                const editing_item = JSON.parse(JSON.stringify(provider));
                this.$set(this, 'editing_item', editing_item);
                if (this.editing_item.weekend_days == '' || !this.editing_item.weekend_days) {
                    this.editing_item.weekend_days = [];
                }
                this.$nextTick(() => {
                    this.show_modal = true;
                });

            },
            deleteItem(provider) {
                this.loading = true;
                let data = {
                    action: 'handle_booking_ajax_endpoint',
                    route: 'delete_provider',
                    provider_id: provider.id
                };
                this.$post(data).then(response => {
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
            },
            getUser(userId) {
                return this.users[userId];
            },
            getServices(servieIds) {
                let serviceList = ''
                servieIds.forEach(element => {
                    if (this.services[element]) {
                        serviceList += this.services[element] + ' ,'
                    }
                });
                return serviceList.slice(0, -1);
            }

        },
        computed: {},
        mounted() {
            this.getItems();
        }
    }
</script>

