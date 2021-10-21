<template>
    <div class="notifications">
        <el-row :gutter="30">
            <el-col :span="6">
                <el-form-item :label="`${name}`">
                    <el-switch active-value="yes" @change="saveEmail" inactive-value="no" v-model="email.status"></el-switch>
                </el-form-item>
            </el-col>
            <el-col :span="16">
                <el-form-item label="Edit Template">
                    <el-button @click="email_editor_visible = true" type="info" size="mini"
                               icon="el-icon-edit"></el-button>
                </el-form-item>
            </el-col>
        </el-row>

        <el-dialog
                top="40px"
                :title="`Edit ${name} Template`"
                :visible.sync="email_editor_visible"
                @open="handleOpen"
                append-to-body
                width="60%">
            <el-form-item class="notification_modal">
                <!--Subject-->
                <el-form-item label="Subject" class="is-required">

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
                    <el-checkbox style="margin-bottom: 10px;" true-label="yes" false-label="no"
                                 v-model="email.asPlainText">
                        Send Email as RAW HTML Format
                    </el-checkbox>
                </el-form-item>
                <el-form-item v-if="time != false" :label="`Send ${time}`">
                    <delay-counter v-model="email.time"></delay-counter>
                </el-form-item>
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
        props: ['emailData', 'name', 'email_key' , 'time'],
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
                            "{inputs.names}": "Name",
                            "{inputs.names.first_name}": "names[First Name]",
                            "{inputs.names.last_name}": "names[Last Name]",
                            "{inputs.email}": "Email",
                            "{inputs.file-upload}": "File Upload",
                            "{inputs.subject}": "Subject",
                            "{inputs.message}": "Your Message",
                            "{inputs.signature}": "Signature"
                        },
                        "title": "Input Options"
                    },
                ],
                email_editor_visible: false,
                email: {
                    status: 'off',
                    subject: '',
                    message: '',
                    body: '',
                    time: '',
                    asPlainText: '',
                    time_direction:''

                }
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
        mounted() {
            if(this.emailData){
                this.email = this.emailData
            }
        }
    }
</script>

<style scoped>

</style>
