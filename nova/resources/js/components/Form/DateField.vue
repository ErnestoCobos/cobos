<template>
    <default-field :errors="errors" :field="field" :show-help-text="showHelpText">
        <template slot="field">
            <div class="flex items-center">
                <date-time-picker
                    ref="dateTimePicker"
                    :alt-format="pickerDisplayFormat"
                    :class="errorClasses"
                    :dateFormat="pickerFormat"
                    :disabled="isReadonly"
                    :dusk="field.attribute"
                    :enable-seconds="false"
                    :enable-time="false"
                    :first-day-of-week="firstDayOfWeek"
                    :name="field.name"
                    :placeholder="placeholder"
                    :value="value"
                    class="w-full form-control form-input form-input-bordered"
                    @change="handleChange"
                />

                <a
                    v-if="field.nullable"
                    :class="{
            'text-50': !value.length,
            'text-black hover:text-danger': value.length,
          }"
                    :title="__('Clear value')"
                    class="p-1 px-2 cursor-pointer leading-none focus:outline-none"
                    href="#"
                    tabindex="-1"
                    @click.prevent="$refs.dateTimePicker.clear()"
                >
                    <icon height="22" type="x-circle" viewBox="0 0 22 22" width="22"/>
                </a>
            </div>
        </template>
    </default-field>
</template>

<script>
import {
    FormField,
    HandlesValidationErrors,
    InteractsWithDates,
} from 'laravel-nova';

export default {
    mixins: [HandlesValidationErrors, FormField, InteractsWithDates],

    methods: {
        /**
         * Update the field's internal value when it's value changes
         */
        handleChange(value)
        {
            this.value = value;
        },
    },

    computed: {
        firstDayOfWeek()
        {
            return this.field.firstDayOfWeek || 0;
        },

        placeholder()
        {
            return this.field.placeholder || moment().format(this.format);
        },

        format()
        {
            return this.field.format || 'YYYY-MM-DD';
        },

        pickerFormat()
        {
            return this.field.pickerFormat || 'Y-m-d';
        },

        pickerDisplayFormat()
        {
            return this.field.pickerDisplayFormat || 'Y-m-d';
        },
    },
};
</script>
