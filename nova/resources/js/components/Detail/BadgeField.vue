<template>
    <panel-item :field="field">
        <template slot="value">
            <badge
                :extra-classes="field.typeClass"
                :label="field.label"
                class="mt-1"
            />
        </template>
    </panel-item>
</template>

<script>
import Badge from '../Badge';

export default {
    components: {
        Badge,
    },

    props: ['resource', 'resourceName', 'resourceId', 'field'],
};
</script>
