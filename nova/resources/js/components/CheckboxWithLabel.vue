<template>
    <label class="flex items-center select-none">
        <checkbox
            :checked="checked"
            :disabled="disabled"
            :name="name"
            class="mr-2"
            @input="$emit('input', $event)"
        />
        <slot/>
    </label>
</template>

<script>
export default {
    props: {
        checked: Boolean,
        name: {type: String, required: false},
        disabled: {
            type: Boolean,
            default: false,
        },
    },
};
</script>
