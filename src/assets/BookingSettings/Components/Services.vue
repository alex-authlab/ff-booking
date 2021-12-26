<template>
    <div v-loading="loading" class="ff_booking_settings_container">

        <div>
            <el-row class="setting_header">
                <el-col :md="14">
                    <h2>Service List</h2>
                </el-col>
                <el-col :md="10">
                  <div class="pull-right">
                    <el-button type="primary" size="medium">
                      <router-link tag="span" to="/service" type="primary" >{{ $t('Add New Service') }} </router-link>
                    </el-button>
                  </div>


                </el-col>
            </el-row>

            <el-table :data="items" stripe border>
                <el-table-column width="100" label="ID" prop="id"/>
                <el-table-column label="Title" prop="title"/>
                <el-table-column label="Duration" prop="duration"/>
                <el-table-column label="Range">
                    <template slot-scope="scope">
                        <span v-if="scope.row.range_type == 'days'">
                            {{ scope.row.allowed_future_days }}
                        </span>
                        <span v-else>
                            {{ formatDate(scope.row.allowed_future_date_range[0]) }} - {{ formatDate(scope.row.allowed_future_date_range[1]) }}
                        </span>

                    </template>
                </el-table-column>

                <el-table-column label="Range">
                    <template slot-scope="scope">
                        {{ scope.row.capacity_type|ucFirst }} {{ booking_types[scope.row.booking_type] }}
                    </template>
                </el-table-column>

                <el-table-column width="220" label="Actions">
                    <template slot-scope="scope">

                            <router-link tag="span" :to="`/service/?service_id=${scope.row.id}`" type="primary" >
                              <el-button type="info" size="mini" icon="el-icon-edit">                        </el-button>

                            </router-link>

                        <remove size="mini" icon="el-icon-delete" @on-confirm="deleteItem(scope.row)"></remove>
                    </template>
                </el-table-column>
            </el-table>
            <div style="margin-top: 20px" class="ff_pagination pull-right">
                <pagination :pagination="pagination" @fetch="getItems"/>
            </div>

        </div>
    </div>
</template>

<script type="text/babel">
    import Pagination from './inc/_Pagination';
    import Remove from "./inc/confirmRemove";
    import Notifications from "./Notifications";
    import DelayCounter from "./inc/delayCounter";

    export default {
        name: 'Services',
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
                items: [],
                booking_types: {
                    'time_slot': 'Time Slot',
                    'date_slot': 'Date Slot',
                },
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
                booking_status: window.ff_booking_settings.booking_status,
                pagination: {
                    current_page: 1,
                    total: 0,
                    per_page: 10
                },
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


