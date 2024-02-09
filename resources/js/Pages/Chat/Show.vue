<template>
  <div class="flex">
    <div class="w-1/2 mr-4 bg-white border border-gray-600 p-4">

    </div>
    <div class="w-1/2 p-2 bg-white border border-gray-600 p-4">

    </div>
  </div>
</template>

<script>
import Main from "@/Layouts/Main.vue";
export default {
  name: "Show",
  layout: Main,
  data() {
    return {

    };
  },


  props: [
      'chat',
  ],

  methods: {

  }
}
</script>

<style scoped>

</style>