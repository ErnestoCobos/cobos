<template>
    <div
        :class="{ 'mr-11': editMode && deleteRowEnabled }"
        class="relative rounded-lg rounded-b-lg bg-30 bg-clip border border-60"
    >
        <slot/>
    </div>
</template>

<script>
export default {
    props: {
        deleteRowEnabled: {
            type: Boolean,
            default: true,
        },
        editMode: {
            type: Boolean,
            default: true,
        },
    },
};
</script>
