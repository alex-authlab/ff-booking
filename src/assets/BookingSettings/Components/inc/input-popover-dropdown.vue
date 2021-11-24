<template>
    <div>
        <el-popover
            ref="input-popover1"
            placement="right-end"
            offset="50"
            popper-class="el-dropdown-list-wrapper"
            v-model="visible"
            trigger="click">
            <div class="el_pop_data_group">
                <div  class="el_pop_data_headings">
                    <ul>
                        <li
                            v-for="(item,item_index) in data"
                            :data-item_index="item_index"
                            :class="(activeIndex == item_index) ? 'active_item_selected' : ''"
                            @click="activeIndex = item_index">
                            {{item.title}}
                        </li>
                    </ul>
                </div>
                <div class="el_pop_data_body">
                    <ul v-for="(item,current_index) in data" v-show="activeIndex == current_index" :class="'el_pop_body_item_'+current_index">
                        <li @click="insertShortcode(code)" v-for="(label,code) in item.shortcodes">{{label}} <span>{{code}}</span></li>
                    </ul>
                </div>
            </div>
        </el-popover>
    </div>
</template>

<script>
    export default {
            name: 'inputPopoverDropdownExtended',
        props: {
            data: Array,
            close_on_insert: {
                type: Boolean,
                default() {
                    return true;
                }
            },
            btnType: {
                type: String,
                default() {
                    return 'success';
                }
            }
        },
        data() {
          return {
              activeIndex: 0,
              visible: false
          }
        },
        methods: {
            insertShortcode(code) {
                this.$emit('command', code);
                if(this.close_on_insert) {
                    this.visible = false;
                }
            }
        },
        mounted() {
        }
    }
</script>
