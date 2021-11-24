<template>
<div>

    <div v-if="fieldType == 'textarea'" class="input-textarea-value">
        <i class="icon el-icon-tickets" v-popover:input-popover></i>
        <el-input :rows="rows" :placeholder="placeholder" type="textarea" v-model="model"></el-input>
    </div>

    <el-input :placeholder="placeholder" v-else v-model="model" :type="fieldType">
    </el-input>
</div>
</template>

<script>
export default {
    name: 'inputPopover',
    props: {
        value : String,
        placeholder: {
            type: String,
            default: ''
        },
        placement: {
            type: String,
            default: 'bottom'
        },
        icon: {
            type: String,
            default: 'el-icon-more'
        },
        fieldType: {
            type: String,
            default: 'text'
        },
        data: Array,
        attrName: {
            type: String,
            default: 'attribute_name'
        },
        rows: {
            type: Number,
            default: 2
        }
    },
    data() {
        return {
            model: this.value,
        }
    },
    watch: {
        model() {
            this.$emit('input', this.model);
        }
    },
    methods: {
        insertShortcode(codeString) {
            if (this.model == undefined) {
                this.model = '';
            }
            this.model += codeString.replace(/param_name/, this.attrName);
        }
    }
}
</script>
