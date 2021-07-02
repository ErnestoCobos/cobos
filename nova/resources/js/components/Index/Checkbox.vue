<template>
    <input
        :checked="checked"
        :disabled="disabled"
        class="checkbox"
        type="checkbox"
        @change="$emit('input', $event)"
    />
</template>

<script>
export default {
    props: {
        disabled: {
            type: Boolean,
            default: false,
        },

        checked: {
            default: false,
        },
    },
};
</script>
