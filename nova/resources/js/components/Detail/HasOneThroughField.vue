<template>
    <resource-index
        :disable-pagination="true"
        :field="field"
        :load-cards="false"
        :relationship-type="'hasOneThrough'"
        :resource-name="field.resourceName"
        :should-override-meta="false"
        :via-relationship="field.hasOneThroughRelationship"
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
