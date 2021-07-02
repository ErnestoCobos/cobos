<template>
    <div>
        <badge :extra-classes="field.typeClass" :label="field.label"/>
    </div>
</template>

<script>
import Badge from '../Badge';

export default {
    components: {
        Badge,
    },

    props: ['resourceName', 'viaResource', 'viaResourceId', 'field'],
};
</script>
