<template>
    <resource-index
        :field="field"
        :initialPerPage="field.perPage || 5"
        :load-cards="false"
        :relationship-type="'hasMany'"
        :resource-name="field.resourceName"
        :should-override-meta="false"
        :via-relationship="field.hasManyRelationship"
        :via-resource="resourceName"
        :via-resource-id="resourceId"
        @actionExecuted="actionExecuted"
    />
</template>

<script>
export default {
    props: ['resourceName', 'resourceId', 'resource', 'field'],

    methods: {
        /**
         * Handle the actionExecuted event and pass it up the chain.
         */
        actionExecuted()
        {
            this.$emit('actionExecuted');
        },
    },
};
</script>
