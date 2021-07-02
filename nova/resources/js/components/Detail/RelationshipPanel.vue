<template>
    <div>
        <!-- <h4 class="text-90 font-normal text-2xl mb-3">{{ panel.name }}</h4> -->

        <div v-for="field in panel.fields" :key="field + resourceId">
            <component
                :is="'detail-' + field.component"
                :field="field"
                :resource="resource"
                :resource-id="resourceId"
                :resource-name="resourceName"
                @actionExecuted="actionExecuted"
            />
        </div>
    </div>
</template>

<script>
import {BehavesAsPanel} from 'laravel-nova';

export default {
    mixins: [BehavesAsPanel],
};
</script>
