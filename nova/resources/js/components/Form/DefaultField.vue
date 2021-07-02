<template>
    <field-wrapper :stacked="field.stacked">
        <div :class="field.stacked ? 'pt-6 w-full' : 'py-6 w-1/5'" class="px-8">
            <slot>
                <form-label
                    :class="{ 'mb-2': showHelpText && field.helpText }"
                    :label-for="field.attribute"
                >
                    {{ fieldLabel }}&nbsp;<span
                    v-if="field.required"
                    class="text-danger text-sm"
                >{{ __('*') }}</span
                >
                </form-label>
            </slot>
        </div>

        <div :class="fieldClasses" class="py-6 px-8">
            <slot name="field"/>

            <help-text
                v-if="showErrors && hasError"
                class="error-text mt-2 text-danger"
            >
                {{ firstError }}
            </help-text>

            <help-text v-if="showHelpText" class="help-text mt-2">
                {{ field.helpText }}
            </help-text>
        </div>
    </field-wrapper>
</template>

<script>
import {HandlesValidationErrors, mapProps} from 'laravel-nova';

export default {
    mixins: [HandlesValidationErrors],

    props: {
        field: {type: Object, required: true},
        fieldName: {type: String},
        showErrors: {type: Boolean, default: true},
        fullWidthContent: {type: Boolean, default: false},
        ...mapProps(['showHelpText']),
    },

    computed: {
        /**
         * Return the label that should be used for the field.
         */
        fieldLabel()
        {
            // If the field name is purposefully an empty string, then let's show it as such
            if (this.fieldName === '') {
                return '';
            }

            return this.fieldName || this.field.name || this.field.singularLabel;
        },

        /**
         * Return the classes that should be used for the field content.
         */
        fieldClasses()
        {
            return this.fullWidthContent
                ? this.field.stacked
                    ? 'w-full'
                    : 'w-4/5'
                : 'w-1/2';
        },
    },
};
</script>
