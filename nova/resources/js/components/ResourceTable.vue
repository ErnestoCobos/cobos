<template>
    <table
        v-if="resources.length > 0"
        :class="[
      `table-${resourceInformation.tableStyle}`,
      resourceInformation.showColumnBorders ? 'table-grid' : '',
    ]"
        cellpadding="0"
        cellspacing="0"
        class="table w-full"
        data-testid="resource-table"
    >
        <thead>
        <tr>
            <!-- Select Checkbox -->
            <th v-if="shouldShowCheckboxes" class="w-16">&nbsp;</th>

            <!-- Field Names -->
            <th v-for="field in fields" :class="`text-${field.textAlign}`">
                <sortable-icon
                    v-if="field.sortable"
                    :resource-name="resourceName"
                    :uri-key="field.sortableUriKey"
                    @reset="resetOrderBy(field)"
                    @sort="requestOrderByChange(field)"
                >
                    {{ field.indexName }}
                </sortable-icon>

                <span v-else>{{ field.indexName }}</span>
            </th>

            <!-- Actions, View, Edit, Delete -->
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <tr
            is="resource-table-row"
            v-for="(resource, index) in resources"
            :key="resource.id.value"
            :actions-are-available="actionsAreAvailable"
            :actions-endpoint="actionsEndpoint"
            :checked="selectedResources.indexOf(resource) > -1"
            :delete-resource="deleteResource"
            :relationship-type="relationshipType"
            :resource="resource"
            :resource-name="resourceName"
            :restore-resource="restoreResource"
            :should-show-checkboxes="shouldShowCheckboxes"
            :testId="`${resourceName}-items-${index}`"
            :update-selection-status="updateSelectionStatus"
            :via-many-to-many="viaManyToMany"
            :via-relationship="viaRelationship"
            :via-resource="viaResource"
            :via-resource-id="viaResourceId"
            @actionExecuted="$emit('actionExecuted')"
        />
        </tbody>
    </table>
</template>

<script>
import {InteractsWithResourceInformation} from 'laravel-nova';

export default {
    mixins: [InteractsWithResourceInformation],

    props: {
        authorizedToRelate: {
            type: Boolean,
            required: true,
        },
        resourceName: {
            default: null,
        },
        resources: {
            default: [],
        },
        singularName: {
            type: String,
            required: true,
        },
        selectedResources: {
            default: [],
        },
        selectedResourceIds: {},
        shouldShowCheckboxes: {
            type: Boolean,
            default: false,
        },
        actionsAreAvailable: {
            type: Boolean,
            default: false,
        },
        viaResource: {
            default: null,
        },
        viaResourceId: {
            default: null,
        },
        viaRelationship: {
            default: null,
        },
        relationshipType: {
            default: null,
        },
        updateSelectionStatus: {
            type: Function,
        },
        actionsEndpoint: {
            default: null,
        },
    },

    data: () => ({
        selectAllResources: false,
        selectAllMatching: false,
        resourceCount: null,
    }),

    methods: {
        /**
         * Delete the given resource.
         */
        deleteResource(resource)
        {
            this.$emit('delete', [resource]);
        },

        /**
         * Restore the given resource.
         */
        restoreResource(resource)
        {
            this.$emit('restore', [resource]);
        },

        /**
         * Broadcast that the ordering should be updated.
         */
        requestOrderByChange(field)
        {
            this.$emit('order', field);
        },

        /**
         * Broadcast that the ordering should be reset.
         */
        resetOrderBy(field)
        {
            this.$emit('reset-order-by', field);
        },
    },

    computed: {
        /**
         * Get all of the available fields for the resources.
         */
        fields()
        {
            if (this.resources) {
                return this.resources[0].fields;
            }
        },

        /**
         * Determine if the current resource listing is via a many-to-many relationship.
         */
        viaManyToMany()
        {
            return (
                this.relationshipType == 'belongsToMany' ||
                this.relationshipType == 'morphToMany'
            );
        },

        /**
         * Determine if the current resource listing is via a has-one relationship.
         */
        viaHasOne()
        {
            return (
                this.relationshipType == 'hasOne' || this.relationshipType == 'morphOne'
            );
        },
    },
};
</script>
