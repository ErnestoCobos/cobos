<template>
    <default-field :errors="errors" :field="field" :show-help-text="showHelpText">
        <template slot="field">
            <input
                :id="field.attribute"
                v-model="value"
                :class="errorClasses"
                :max="inputMax"
                :min="inputMin"
                :placeholder="field.name"
                :step="inputStep"
                :type="inputType"
                class="w-full form-control form-input form-input-bordered"
            />
        </template>
    </default-field>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova';

export default {
    mixins: [HandlesValidationErrors, FormField],

    computed: {
        inputType()
        {
            return this.field.type || 'text';
        },

        inputStep()
        {
            return this.field.step;
        },

        inputMin()
        {
            return this.field.min;
        },

        inputMax()
        {
            return this.field.max;
        },
    },
};
</script>
