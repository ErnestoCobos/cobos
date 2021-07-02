<template>
    <icon
        v-if="value"
        :height="height"
        :viewBox="viewBox"
        :width="width"
        class="text-success"
        type="check-circle"
    />
    <icon
        v-else
        :height="height"
        :viewBox="viewBox"
        :width="width"
        class="text-danger"
        type="x-circle"
    />
</template>

<script>
export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        viewBox: {
            default: '0 0 24 24',
        },

        height: {
            default: 24,
        },

        width: {
            default: 24,
        },
    },
};
</script>
