<template>
  <span>
    <select
        v-if="actions.length > 1"
        ref="selectBox"
        class="
        rounded-sm
        select-box-sm
        mr-2
        h-6
        text-xs
        appearance-none
        bg-40
        pl-2
        pr-6
        active:outline-none active:shadow-outline
        focus:outline-none focus:shadow-outline
      "
        dusk="inline-action-select"
        style="max-width: 90px"
        @change="handleSelectionChange"
    >
      <option disabled selected>{{ __('Actions') }}</option>
      <option
          v-for="action in actions"
          :key="action.uriKey"
          :value="action.uriKey"
      >
        {{ action.name }}
      </option>
    </select>

    <button
        v-for="action in actions"
        v-else
        :key="action.uriKey"
        :class="action.class"
        :data-testid="action.uriKey"
        class="btn btn-xs mr-1"
        dusk="run-inline-action-button"
        @click="executeSingleAction(action)"
    >
      {{ action.name }}
    </button>

      <!-- Action Confirmation Modal -->
    <portal to="modals">
      <component
          :is="selectedAction.component"
          v-if="confirmActionModalOpened"
          :action="selectedAction"
          :endpoint="actionsEndpoint"
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
  </span>
</template>

<script>
import HandlesActions from '@/mixins/HandlesActions';

export default {
    mixins: [HandlesActions],

    props: {
        resource: {},
        actions: {},
    },

    data: () => ({
        showActionResponseModal: false,
        actionResponseData: {},
    }),

    methods: {
        handleSelectionChange(event)
        {
            this.selectedActionKey = event.target.value;
            this.determineActionStrategy();
            this.$refs.selectBox.selectedIndex = 0;
        },

        executeSingleAction(action)
        {
            this.selectedActionKey = action.uriKey;
            this.determineActionStrategy();
        },
    },

    computed: {
        selectedResources()
        {
            return [this.resource.id.value];
        },
    },
};
</script>
