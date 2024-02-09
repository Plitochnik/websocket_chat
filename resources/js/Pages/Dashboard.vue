<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
</script>

<template>
  <Head title="Dashboard"/>

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="flex pa-0">
            <div class="w-1/2 bg-white border border-gray-600 p-4">
              <h3 class="mb-3">Friends</h3>
              <div v-if="users">
                <div v-for="user in users" class="flex pb-2 mb-2 border-b border-gray-300 items-center">
                  <p class="mr-4">{{ user.name }}</p>
                  <a @click.prevent="storeChat(user.id)"
                     class="inline-block bg-sky-500 text-white text-xs px-3 py-2 rounded-lg" href="#">Message</a>
                </div>
              </div>
            </div>
            <div v-if="messages.length" class="chat w-1/2 mr-4 bg-white border border-gray-600 p-4 overflow-auto">
              <div v-for="(message, index) in messages"
                   :key="index"
                   class="my-2"
              >
                <div :class="message.owner === 'user' ? 'text-right' : 'text-left'">
                  <span class="inline-block px-3 py-1 rounded-lg"
                        :class="message.owner === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-300 text-black'">
                    {{ message.text }}
                  </span>
                </div>
              </div>
            </div>
            <div v-else class="chat w-1/2 mr-4 bg-white border border-gray-600 p-4 overflow-auto">
              Select a friend to continue or start a new chat
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
export default {
  name: "Dashboard",
  data() {
    return {
      users: [],
      messages: [],
    };
  },
  mounted() {
    this.getChats();
  },
  created() {
    // res.message instead of res.data
    // window.Echo.channel('store-message')
    //     .listen('.store-message', res => {
    //       console.log(res);
    //     })

    // listen to user's channel
    // window.Echo.channel(`users.${this.$page.props.auth.user.id}`)
    //     .listen('.store-message', res => {
    //       console.log(res);
    //     })
  },
  methods: {
    getChats() {
      axios.get('/api/get-chats')
          .then(response => {
            this.users = response.data.users;
          })
          .catch(error => {
            console.error('Error fetching users:', error);
          });
    },
    storeChat(id) {
      axios.post('/api/chats', {
        title: null,
        users: [id],
      }).then((response) => {

      });
    }
  }

}
</script>
