<template>
    <panel-item :field="field">
        <icon
            v-if="field.value"
            slot="value"
            class="text-success"
            height="24"
            type="check-circle"
            viewBox="0 0 24 24"
            width="24"
        />
        <icon
            v-else
            slot="value"
            class="text-danger"
            height="24"
            type="x-circle"
            viewBox="0 0 24 24"
            width="24"
        />
    </panel-item>
</template>

<script>
export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],

    computed: {
        label()
        {
            return this.field.value == true ? this.__('Yes') : this.__('No');
        },
    },
};
</script>
