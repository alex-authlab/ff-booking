<template>
    <div>
        <el-input-number v-model="delayValue" @input="onInput" :min="1" :max="999"></el-input-number>
        <span>
           <el-select placeholer="Select" @input="onInput" v-model="selectedUnit">
             <el-option v-for="unit in delayUnits" :key="unit" :label="unit"
                        :value="unit"></el-option>
          </el-select>
        </span>
    </div>
</template>
<script>
    export default {
        name: 'delayCounter',
        props: {
            value: {
                type: String,
                required: true,
            },
        },
        data() {
            return {
                delayValue: '1',
                selectedUnit: 'Day',
                delayUnits: [
                    'Day',
                    'Week',
                    'Month',
                    'Year',
                ],
            }
        },
        methods: {
            onInput(event) {

                let value = this.delayValue + ' ' + this.selectedUnit;
                this.$emit('input', value)
            }
        },
        computed: {},
        mounted() {
            this.delayValue = parseInt(this.value.split(" ")[0]);
            this.selectedUnit = this.value.split(" ")[1];
        }
    }
</script>
