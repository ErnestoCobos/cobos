<template>
    <create-form
        :mode="mode"
        :resource-name="resourceName"
        :should-override-meta="mode == 'form' ? true : false"
        :update-form-status="updateFormStatus"
        :via-relationship="viaRelationship"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        @resource-created="handleResourceCreated"
        @cancelled-create="handleCancelledCreate"
    />
</template>

<script>
import {mapProps, PreventsFormAbandonment} from 'laravel-nova';

export default {
    mixins: [PreventsFormAbandonment],

    props: {
        mode: {
            type: String,
            default: 'form',
            validator: val => ['modal', 'form'].includes(val),
        },

        ...mapProps([
            'resourceName',
            'viaResource',
            'viaResourceId',
            'viaRelationship',
        ]),
    },

    methods: {
        handleResourceCreated({redirect, id})
        {
            this.canLeave = true;

            if (this.mode == 'form') {
                return this.$router.push({path: redirect});
            }

            return this.$emit('refresh', {redirect, id});
        },

        handleCancelledCreate()
        {
            if (this.mode == 'form') {
                return this.$router.back();
            }

            return this.$emit('cancelled-create');
        },
    },
};
</script>
