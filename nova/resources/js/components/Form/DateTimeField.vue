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
                    :first-day-of-week="firstDayOfWeek"
                    :hour-increment="pickerHourIncrement"
                    :minute-increment="pickerMinuteIncrement"
                    :name="field.name"
                    :placeholder="placeholder"
                    :twelve-hour-time="usesTwelveHourTime"
                    :value="localizedValue"
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

                <span class="text-80 text-sm ml-2">({{ userTimezone }})</span>
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

    data: () => ({localizedValue: ''}),

    methods: {
        /*
         * Set the initial value for the field
         */
        setInitialValue()
        {
            // Set the initial value of the field
            this.value = this.field.value || '';

            // If the field has a value let's convert it from the app's timezone
            // into the user's local time to display in the field
            if (this.value !== '') {
                this.localizedValue = this.fromAppTimezone(this.value);
            }
        },

        /**
         * On save, populate our form data
         */
        fill(formData)
        {
            formData.append(this.field.attribute, this.value || '');
        },

        /**
         * Update the field's internal value when it's value changes
         */
        handleChange(value)
        {
            this.value = this.toAppTimezone(value);
        },
    },

    computed: {
        firstDayOfWeek()
        {
            return this.field.firstDayOfWeek || 0;
        },

        format()
        {
            return this.field.format || 'YYYY-MM-DD HH:mm:ss';
        },

        placeholder()
        {
            return this.field.placeholder || moment().format(this.format);
        },

        pickerFormat()
        {
            return this.field.pickerFormat || 'Y-m-d H:i:S';
        },

        pickerDisplayFormat()
        {
            return this.field.pickerDisplayFormat || 'Y-m-d H:i:S';
        },

        pickerHourIncrement()
        {
            return this.field.pickerHourIncrement || 1;
        },

        pickerMinuteIncrement()
        {
            return this.field.pickerMinuteIncrement || 5;
        },
    },
};
</script>
