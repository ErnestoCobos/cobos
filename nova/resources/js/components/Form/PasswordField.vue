<template>
    <default-field :errors="errors" :field="field" :show-help-text="showHelpText">
        <template slot="field">
            <input
                :id="field.attribute"
                v-model="value"
                :class="errorClasses"
                :disabled="isReadonly"
                :dusk="field.attribute"
                :placeholder="field.name"
                autocomplete="new-password"
                class="w-full form-control form-input form-input-bordered"
                type="password"
            />
        </template>
    </default-field>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova';

export default {
    mixins: [HandlesValidationErrors, FormField],
};
</script>
