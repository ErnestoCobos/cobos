<template>
    <default-field :errors="errors" :field="field" :show-help-text="showHelpText">
        <template slot="field">
            <checkbox
                :id="field.attribute"
                :checked="checked"
                :disabled="isReadonly"
                :name="field.name"
                class="mt-2"
                @input="toggle"
            />
        </template>
    </default-field>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova';

export default {
    mixins: [HandlesValidationErrors, FormField],

    data: () => ({
        value: false,
    }),

    mounted()
    {
        this.value = this.field.value || false;

        this.field.fill = formData => {
            formData.append(this.field.attribute, this.trueValue);
        };
    },

    methods: {
        toggle()
        {
            this.value = !this.value;
        },
    },

    computed: {
        checked()
        {
            return Boolean(this.value);
        },

        trueValue()
        {
            return +this.checked;
        },
    },
};
</script>
