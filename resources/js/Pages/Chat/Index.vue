<template>
  <div class="flex">
    <div class="w-1/2 mr-4 bg-white border border-gray-600 p-4">
      Lorem ipsum dolor sit amet, consectetur adipisicing elit. Et exercitationem fugit illum, in neque officiis repudiandae saepe tempore. Architecto dignissimos, fugiat harum inventore laudantium magni neque nisi placeat similique tempore.
    </div>
    <div class="w-1/2 p-2 bg-white border border-gray-600 p-4">
      <h3>Users</h3>
      <div v-if="users">
        <div v-for="user in users" class="flex pb-2 mb-2 border-b border-gray-300 items-center">
          <p class="mr-2">{{ user.id }}</p>
          <p class="mr-4">{{ user.name }}</p>
          <a @click.prevent="store(user.id)" class="inline-block bg-sky-500 text-white text-xs px-3 py-2 rounded-lg" href="#">Message</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Main from "@/Layouts/Main.vue";
export default {
  name: "Index",
  layout: Main,
  data() {
    return {

    };
  },
  props: [
      'users',
  ],
  created() {
    // res.message instead of res.data
    window.Echo.channel('store-message')
        .listen('.store-message', res => {
          console.log(res);
        })

    // listen to user's channel
    // window.Echo.channel(`users.${this.$page.props.auth.user.id}`)
    //     .listen('.store-message', res => {
    //       console.log(res);
    //     })
  },

  methods: {
    store(id) {
      this.$inertia.post('/chats', {
        title: null,
        users: [id],
      });
    }
  }

}
</script>

<style scoped>

</style>