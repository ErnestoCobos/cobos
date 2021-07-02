<template>
    <div>
        <div
            v-if="actions.length > 0 || availablePivotActions.length > 0"
            class="flex items-center mr-3"
        >
            <select
                ref="selectBox"
                v-model="selectedActionKey"
                class="form-control form-select mr-2"
                data-testid="action-select"
                dusk="action-select"
            >
                <option disabled selected value="">{{ __('Select Action') }}</option>

                <optgroup
                    v-if="availableActions.length > 0"
                    :label="resourceInformation.singularLabel"
                >
                    <option
                        v-for="action in availableActions"
                        :key="action.urikey"
                        :selected="action.uriKey == selectedActionKey"
                        :value="action.uriKey"
                    >
                        {{ action.name }}
                    </option>
                </optgroup>

                <optgroup
                    v-if="availablePivotActions.length > 0"
                    :label="pivotName"
                    class="pivot-option-group"
                >
                    <option
                        v-for="action in availablePivotActions"
                        :key="action.urikey"
                        :selected="action.uriKey == selectedActionKey"
                        :value="action.uriKey"
                    >
                        {{ action.name }}
                    </option>
                </optgroup>

                <template v-if="availableStandaloneActions.length > 0">
                    <optgroup
                        v-if="selectedResources.length > 0"
                        :label="__('Standalone Actions')"
                        class="standalone-option-group"
                    >
                        <option
                            v-for="action in availableStandaloneActions"
                            :key="action.urikey"
                            :selected="action.uriKey == selectedActionKey"
                            :value="action.uriKey"
                        >
                            {{ action.name }}
                        </option>
                    </optgroup>
                    <template v-else>
                        <option
                            v-for="action in availableStandaloneActions"
                            :key="action.urikey"
                            :selected="action.uriKey == selectedActionKey"
                            :value="action.uriKey"
                        >
                            {{ action.name }}
                        </option>
                    </template>
                </template>
            </select>

            <button
                :class="{ 'btn-disabled': !selectedAction }"
                :disabled="!selectedAction"
                :title="__('Run Action')"
                class="
          btn btn-default btn-primary
          flex
          items-center
          justify-center
          px-3
        "
                data-testid="action-confirm"
                dusk="run-action-button"
                @click.prevent="determineActionStrategy"
            >
                <icon class="text-white" style="margin-left: 7px" type="play"/>
            </button>
        </div>

        <!-- Action Confirmation Modal -->
        <portal to="modals" transition="fade-transition">
            <component
                :is="selectedAction.component"
                v-if="confirmActionModalOpened"
                :action="selectedAction"
                :errors="errors"
                :resource-name="resourceName"
                :selected-resources="selectedResources"
                :working="working"
                class="text-left"
                @close="closeConfirmationModal"
                @confirm="executeAction"
            />

            <component
                :is="actionResponseData.modal"
                v-if="showActionResponseModal"
                :data="actionResponseData"
                @close="closeActionResponseModal"
            />
        </portal>
    </div>
</template>

<script>
import _ from 'lodash';
import HandlesActions from '@/mixins/HandlesActions';
import {InteractsWithResourceInformation} from 'laravel-nova';

export default {
    mixins: [InteractsWithResourceInformation, HandlesActions],

    props: {
        selectedResources: {
            type: [Array, String],
            default: () => [],
        },
        pivotActions: {},
        pivotName: String,
    },

    data: () => ({
        showActionResponseModal: false,
        actionResponseData: {},
    }),

    watch: {
        /**
         * Watch the actions property for changes.
         */
        actions()
        {
            this.selectedActionKey = '';
            this.initializeActionFields();
        },

        /**
         * Watch the pivot actions property for changes.
         */
        pivotActions()
        {
            this.selectedActionKey = '';
            this.initializeActionFields();
        },
    },
};
</script>
