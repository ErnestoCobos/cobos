<template>
    <div>
        <!-- Validation Errors -->
        <div v-if="errors.length > 0" class="row">
            <div class="col-6 alert alert-danger">
                <strong>{{ __('Whoops!') }}</strong>
                {{ __('Something went wrong.') }}

                <br/><br/>

                <!-- Error Listing -->
                <ul style="margin-bottom: 0">
                    <li v-for="error in errors">{{ error }}</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['errors'],
};
</script>
