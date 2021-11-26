<template>
    <div class="notifications">
        <el-row :gutter="30">
            <el-col :span="6">
                <el-form-item :label="`${name}`">
                    <el-switch active-value="yes" @change="saveEmail" inactive-value="no"
                               v-model="email.status"></el-switch>
                </el-form-item>
            </el-col>
            <el-col :span="16">
                <el-form-item >
                    <el-button @click="email_editor_visible = true" type="info" size="mini"
                               icon="el-icon-edit"></el-button>
                </el-form-item>
            </el-col>
        </el-row>
        <!--email editor -->
        <el-dialog
                top="40px"
                :title="`Edit ${name} Template`"
                :visible.sync="email_editor_visible"
                @open="handleOpen"
                append-to-body
                width="60%">
            <!--Subject-->
            <el-form-item label="Subject">
                <input-popover fieldType="text" v-model="email.subject"></input-popover>
            </el-form-item>
            <!--message-->
            <el-form-item label="Email Body">
                <input-popover :rows="6" v-if="email.asPlainText == 'yes'" fieldType="textarea" v-model="email.message" placeholder="Email Body HTML"></input-popover>
                <wp_editor
                        ref="emailEditor"
                        :key="componentKey"
                        v-else
                        :height="200"
                        v-model="email.body">
                </wp_editor>
            </el-form-item>
            

            <el-form-item v-if="time != false" :label="`Send ${time}`">
                <delay-counter v-model="email.time"></delay-counter>
            </el-form-item>
            <el-collapse>
                <el-collapse-item title="Show Shortcodes" name="1">
                    <div>
                        <ul class="">
                            <li v-for="item in editorShortcodes">
                                <span v-if="item.length > 1" class="group-title">{{ item.title }}</span>
                                <ul>
                                    <li v-for="title, code in item.shortcodes">
                                        {{ title }} - {{code}}
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </el-collapse-item>
            </el-collapse>
            <span slot="footer" class="dialog-footer">
              <el-button type="primary" v-loading="saving" @click="saveEmail()">Save</el-button>
          </span>
        </el-dialog>
    </div>
</template>

<script>
    import wpEditor from "./_wp_editor";
    import inputPopover from "./input-popover";
    import delayCounter from "./delayCounter";

    export default {
        name: "Notification",
        props: {
            name: String,
            email_key: String,
            emailData: Object,
            time: String | Boolean,
        },
        components: {
            'wp_editor': wpEditor,
            inputPopover,
            delayCounter
        },
        data() {
            return {
                componentKey: false,
                saving: false,
                editorShortcodes: [
                    {
                        "shortcodes": {
                            "{ff_booking_info}": "Booking Info",
                            "{ff_booking_info_page_link}" : "Booking Info Page Link",
                           " {ff_booking_date_time}": "Booking Date Time",
                            "{ff_booking_service}": "Booking Service",
                            "{ff_booking_provider}": "Booking Provider",
                            "{ff_booking_user_email}": "Booking User Email",
                            "{ff_booking_user_name}": "Booking User Name",
                        },
                        "title": "Booking Shortcodes"
                    },
                ],
                email_editor_visible: false,
            }
        },
        methods: {
            saveEmail() {
                this.email_editor_visible = false;
            },
            handleOpen() {
                this.componentKey = !this.componentKey; //to rerender component always
            }

        },
        watch: {
            emailData: {
                handler(newval, oldval) {
                    this.email = newval
                },
                deep: true
            }
        },
        computed: {
            showButtons() {
                if (this.email_key != 'query_email') {
                    return true;
                }
                return false;

            },
            email :{
                get(){
                    return this.emailData
                },
                set(data){
                    this.$emit('update-email', this.email_key , data )
                }
            }
        }
    }
</script>
