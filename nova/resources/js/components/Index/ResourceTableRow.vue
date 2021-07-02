<template>
    <tr
        :data-pivot-id="resource['id'].pivotValue"
        :dusk="resource['id'].value + '-row'"
    >
        <!-- Resource Selection Checkbox -->
        <td v-if="shouldShowCheckboxes" class="w-16">
            <checkbox
                v-if="shouldShowCheckboxes"
                :checked="checked"
                :data-testid="`${testId}-checkbox`"
                :dusk="`${resource['id'].value}-checkbox`"
                @input="toggleSelection"
            />
        </td>

        <!-- Fields -->
        <td v-for="field in resource.fields">
            <component
                :is="'index-' + field.component"
                :class="`text-${field.textAlign}`"
                :field="field"
                :resource="resource"
                :resource-name="resourceName"
                :via-resource="viaResource"
                :via-resource-id="viaResourceId"
            />
        </td>

        <td class="td-fit text-right pr-6 align-middle">
            <div class="inline-flex items-center">
                <!-- Actions Menu -->
                <inline-action-selector
                    v-if="availableActions.length > 0"
                    :actions="availableActions"
                    :endpoint="actionsEndpoint"
                    :resource="resource"
                    :resource-name="resourceName"
                    class="mr-3"
                    @actionExecuted="$emit('actionExecuted')"
                />

                <!-- View Resource Link -->
                <span v-if="resource.authorizedToView" class="inline-flex">
          <router-link
              v-tooltip.click="__('View')"
              :data-testid="`${testId}-view-button`"
              :dusk="`${resource['id'].value}-view-button`"
              :to="{
              name: 'detail',
              params: {
                resourceName: resourceName,
                resourceId: resource['id'].value,
              },
            }"
              class="
              cursor-pointer
              text-70
              hover:text-primary
              mr-3
              inline-flex
              items-center
            "
          >
            <icon height="18" type="view" view-box="0 0 22 16" width="22"/>
          </router-link>
        </span>

                <span v-if="resource.authorizedToUpdate" class="inline-flex">
          <!-- Edit Pivot Button -->
          <router-link
              v-if="
              relationshipType == 'belongsToMany' ||
              relationshipType == 'morphToMany'
            "
              v-tooltip.click="__('Edit Attached')"
              :dusk="`${resource['id'].value}-edit-attached-button`"
              :to="{
              name: 'edit-attached',
              params: {
                resourceName: viaResource,
                resourceId: viaResourceId,
                relatedResourceName: resourceName,
                relatedResourceId: resource['id'].value,
              },
              query: {
                viaRelationship: viaRelationship,
                viaPivotId: resource['id'].pivotValue,
              },
            }"
              class="inline-flex cursor-pointer text-70 hover:text-primary mr-3"
          >
            <icon type="edit"/>
          </router-link>

                    <!-- Edit Resource Link -->
          <router-link
              v-else
              v-tooltip.click="__('Edit')"
              :dusk="`${resource['id'].value}-edit-button`"
              :to="{
              name: 'edit',
              params: {
                resourceName: resourceName,
                resourceId: resource['id'].value,
              },
              query: {
                viaResource: viaResource,
                viaResourceId: viaResourceId,
                viaRelationship: viaRelationship,
              },
            }"
              class="inline-flex cursor-pointer text-70 hover:text-primary mr-3"
          >
            <icon type="edit"/>
          </router-link>
        </span>

                <!-- Delete Resource Link -->
                <button
                    v-if="
            resource.authorizedToDelete &&
            (!resource.softDeleted || viaManyToMany)
          "
                    v-tooltip.click="__(viaManyToMany ? 'Detach' : 'Delete')"
                    :data-testid="`${testId}-delete-button`"
                    :dusk="`${resource['id'].value}-delete-button`"
                    class="
            inline-flex
            appearance-none
            cursor-pointer
            text-70
            hover:text-primary
            mr-3
          "
                    @click.prevent="openDeleteModal"
                >
                    <icon/>
                </button>

                <!-- Restore Resource Link -->
                <button
                    v-if="
            resource.authorizedToRestore &&
            resource.softDeleted &&
            !viaManyToMany
          "
                    v-tooltip.click="__('Restore')"
                    :dusk="`${resource['id'].value}-restore-button`"
                    class="appearance-none cursor-pointer text-70 hover:text-primary mr-3"
                    @click.prevent="openRestoreModal"
                >
                    <icon height="21" type="restore" with="20"/>
                </button>

                <portal
                    v-if="deleteModalOpen || restoreModalOpen"
                    to="modals"
                    transition="fade-transition"
                >
                    <delete-resource-modal
                        v-if="deleteModalOpen"
                        :mode="viaManyToMany ? 'detach' : 'delete'"
                        @close="closeDeleteModal"
                        @confirm="confirmDelete"
                    >
                        <div slot-scope="{ uppercaseMode, mode }" class="p-8">
                            <heading :level="2" class="mb-6">{{
                                    __(uppercaseMode + ' Resource')
                                }}
                            </heading>
                            <p class="text-80 leading-normal">
                                {{ __('Are you sure you want to ' + mode + ' this resource?') }}
                            </p>
                        </div>
                    </delete-resource-modal>

                    <restore-resource-modal
                        v-if="restoreModalOpen"
                        @close="closeRestoreModal"
                        @confirm="confirmRestore"
                    >
                        <div class="p-8">
                            <heading :level="2" class="mb-6">{{
                                    __('Restore Resource')
                                }}
                            </heading>
                            <p class="text-80 leading-normal">
                                {{ __('Are you sure you want to restore this resource?') }}
                            </p>
                        </div>
                    </restore-resource-modal>
                </portal>
            </div>
        </td>
    </tr>
</template>

<script>
export default {
    props: [
        'testId',
        'deleteResource',
        'restoreResource',
        'resource',
        'resourcesSelected',
        'resourceName',
        'relationshipType',
        'viaRelationship',
        'viaResource',
        'viaResourceId',
        'viaManyToMany',
        'checked',
        'actionsAreAvailable',
        'actionsEndpoint',
        'shouldShowCheckboxes',
        'updateSelectionStatus',
        'queryString',
    ],

    data: () => ({
        deleteModalOpen: false,
        restoreModalOpen: false,
    }),

    methods: {
        /**
         * Select the resource in the parent component
         */
        toggleSelection()
        {
            this.updateSelectionStatus(this.resource);
        },

        openDeleteModal()
        {
            this.deleteModalOpen = true;
        },

        confirmDelete()
        {
            this.deleteResource(this.resource);
            this.closeDeleteModal();
        },

        closeDeleteModal()
        {
            this.deleteModalOpen = false;
        },

        openRestoreModal()
        {
            this.restoreModalOpen = true;
        },

        confirmRestore()
        {
            this.restoreResource(this.resource);
            this.closeRestoreModal();
        },

        closeRestoreModal()
        {
            this.restoreModalOpen = false;
        },
    },

    computed: {
        availableActions()
        {
            return _.filter(this.resource.actions, a => a.showOnTableRow);
        },
    },
};
</script>
