<template>
    <div :errors="errors" class="hidden">
        <input :value="value" type="hidden"/>
    </div>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova';

export default {
    mixins: [FormField, HandlesValidationErrors],
};
</script>
