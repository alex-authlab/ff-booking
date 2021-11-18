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
                <el-form-item label="Edit Template">
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

                <input-popover fieldType="text"
                               v-model="email.subject"
                               :data="editorShortcodes"
                ></input-popover>
            </el-form-item>
            <!--message-->
            <el-form-item label="Email Body" class="is-required">
                <input-popover :rows="10" v-if="email.asPlainText == 'yes'" fieldType="textarea"
                               v-model="email.message"
                               placeholder="Email Body HTML"
                               :data="editorShortcodes"
                ></input-popover>

                <wp_editor
                        ref="emailEditor"
                        :key="componentKey"
                        v-else
                        :editorShortcodes="editorShortcodes"
                        :height="300"
                        v-model="email.body">
                </wp_editor>
            </el-form-item>
            

            <el-form-item v-if="time != false" :label="`Send ${time}`">
                <delay-counter v-model="email.time"></delay-counter>
            </el-form-item>
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
                email: {
                    subject: 'Booking Notification',
                    message: '',
                    body: '{ff_booking_info}',
                    time: '',
                    asPlainText: '',
                    status: '',
                    time_direction: ''
                },
                editorShortcodes: [
                    {
                        "shortcodes": {
                            "{ff_booking_info}": "Booking Info",
                        },
                        "title": "Booking Shortcodes"
                    },
                ],
                email_editor_visible: false,
            }
        },
        methods: {
            saveEmail() {
                let emailData = {
                    'key': this.email_key,
                    'value': this.email
                };
                this.$emit('update-email', emailData)
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

            }
        }
    }
</script>
