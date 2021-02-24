<template>
    <div class="ff_payment_general_settings">

            <el-row class="setting_header">
                <el-col :md="18">
                    <h2>
                        Service List
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <h3>Error Message Placement</h3>
                                <p>These Settings will be used as default settings of a new form.<br/>You can customize
                                    layout settings for each page from form's settings page</p>
                            </div>
                            <i class="el-icon-info el-text-info"></i>
                        </el-tooltip>
                    </h2>
                </el-col>
                <el-col :md="6" class="action-buttons clearfix mb15">
                    <el-button size="medium" class="pull-right" type="primary"  @click="formVisible = true">
                        New +
                    </el-button>
                </el-col>
            </el-row>

            <el-dialog title="New service" :visible.sync="formVisible" >

                    <el-form :model="service" :rules="rules" ref="service" label-width="120px" class="form-service"  v-loading="loadingForm">
                    <el-form-item label="Name" prop="name">
                        <el-input v-model="service.name"></el-input>
                    </el-form-item>

                    <el-form-item label="Start Time" prop="startTime">
                        <el-time-select
                                placeholder="Start time"
                                v-model="service.startTime"
                                :picker-options="{
                                  start: '06:00',
                                  step: '00:30',
                                  end: '23:30',
                                  maxTime:service.endTime
                                }">
                        </el-time-select>
                    </el-form-item>

                    <el-form-item label="End Time" prop="endTime">
                        <el-time-select
                                placeholder="End time"
                                v-model="service.endTime"
                                :picker-options="{
                              start: '07:00',
                              step: '00:30',
                              end: '24:00',
                              minTime : service.startTime
                            }">
                        </el-time-select>
                    </el-form-item>

                    <el-form-item label="Duration" prop="duration">
                        <el-time-select
                                placeholder="Duration"
                                v-model="service.duration"
                                :picker-options="{
                          start: '00:00',
                          step: '00:30',
                          end: '24:00',
                        }">
                        </el-time-select>
                    </el-form-item>
                    <el-form-item label="Details" prop="details">
                        <el-input
                                type="textarea"
                                :rows="2"
                                placeholder="Details"
                                v-model="service.details">
                        </el-input>
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="submitForm('service')">Create</el-button>
                        <el-button @click="resetForm('service')">Reset</el-button>
                    </el-form-item>
                </el-form>
                    <span slot="footer" class="dialog-footer">
                    </span>
            </el-dialog>

            <div class="wpf_settings_section">

                <el-table
                        v-loading = outerLoading
                        :data="tableData.filter(data => !search || data.name.toLowerCase().includes(search.toLowerCase()))"
                        style="width: 100%">

                    <el-table-column
                            label="Name"
                            prop="name">
                    </el-table-column>
                    <el-table-column
                            align="right">
                        <template slot="header" slot-scope="scope">
                            <el-input
                                    v-model="search"
                                    size="mini"
                                    placeholder="Type to search"/>
                        </template>
                        <template slot-scope="scope">
                            <el-button
                                    size="mini"
                                    @click="handleEdit(scope.$index, scope.row)">Edit</el-button>
                            <el-button
                                    size="mini"
                                    type="danger"
                                    @click="handleDelete(scope.$index, scope.row)">Delete</el-button>
                        </template>
                    </el-table-column>
                </el-table>

            </div>

        <div class="action_right">
            <el-button @click="saveSettings()" type="primary" size="small">Save Settings</el-button>
        </div>


    </div>
</template>

<script type="text/babel">
    import PhotoUploader from "./PhotoUploader";
    export default {
        name: 'bookings',
        props: [''],
        components: {
            PhotoUploader
        },
        data() {
            return {
                tableData: [],
                search: '',
                loadingForm:false,
                outerLoading:false,
                service: {

                },

                rules: {
                    name: [
                        { required: true, message: 'Please input this item', trigger: 'blur' },
                    ],
                    startTime: [
                        { required: true, message: 'Please input this item', trigger: 'blur' },
                    ],
                    endTime: [
                        { required: true, message: 'Please input this item', trigger: 'blur' },
                    ],
                    duration: [
                        { required: true, message: 'Please input this item', trigger: 'blur' },
                    ],

                },
                formVisible:false,


            }
        },
        methods: {
            submitForm(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                       this.save();
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            resetForm(formName) {
                this.$refs[formName].resetFields();
            },
            handleEdit(index, row) {
                console.log(index, row);
            },
            handleDelete(index, row) {
                console.log(index, row);
            },
            getList(){
                this.outerLoading = true;
                jQuery.get(window.ajaxurl, {
                        action: 'handle_booking_ajax_endpoint',
                        route: 'get_service',
                    })
                    .then(response => {
                        this.outerLoading = false;
                        this.tableData = response.data.data;
                    });
            },
            save() {
                this.loadingForm = true;
                jQuery.post(window.ajaxurl, {
                        action: 'handle_booking_ajax_endpoint',
                        route: 'update_service',
                        data: this.service
                    })
                    .then(response => {
                        this.$notify.success(response.data.message);
                        this.loadingForm = false;
                        this.formVisible = false;
                    });
            }
        },
        mounted() {
            this.getList();
        }
    }
</script>

<style lang="scss">
    .item_full_width {
        width: 100%;
    }

</style>
