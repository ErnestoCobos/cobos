<template>
    <default-field
        :errors="errors"
        :field="field"
        :full-width-content="true"
        :show-help-text="showHelpText"
    >
        <template slot="field">
      <textarea
          :id="field.attribute"
          v-bind="extraAttributes"
          :dusk="field.attribute"
          :value="value"
          class="w-full form-control form-input form-input-bordered py-3 h-auto"
          @input="handleChange"
      />
        </template>
    </default-field>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova';

export default {
    mixins: [FormField, HandlesValidationErrors],

    computed: {
        defaultAttributes()
        {
            return {
                rows: this.field.rows,
                class: this.errorClasses,
                placeholder: this.field.name,
            };
        },

        extraAttributes()
        {
            const attrs = this.field.extraAttributes;

            return {
                ...this.defaultAttributes,
                ...attrs,
            };
        },
    },
};
</script>
