<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
</script>

<template>
  <Head title="Dashboard"/>

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Messenger</h2>
    </template>

    <div class="py-12">
      <div class="main-block max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="flex pa-0">
            <div class="w-1/2 bg-white border border-gray-600 p-4">
              <h3 class="mb-3">Friends</h3>
              <div v-if="users">
                <div v-for="user in users" class="flex pb-2 mb-2 border-b border-gray-300 items-center justify-between">
                  <div class="flex items-center">
                    <p class="mr-4">{{ user.name }}</p>
                    <button
                        @click.prevent="startChat(user.id)"
                        :class="chatSelected && selectedUser === user.id ? 'bg-yellow-400' : 'bg-sky-500'"
                        class="inline-block bg-sky-500 text-white text-xs px-3 py-2 rounded-lg">
                      Message
                    </button>
                  </div>
                  <button
                      v-if="selectedUser === user.id"
                      @click="clearChat"
                      class="inline-block bg-red-500 text-white text-xs px-3 py-2 rounded-lg">
                    Clear
                  </button>
                </div>
              </div>
            </div>
            <div v-if="chatSelected"
                 class="w-1/2 mr-4 bg-white border border-gray-600 p-4 overflow-auto flex flex-col justify-between">
              <!--      Chat        -->
              <div class="chat" style="height: 300px; overflow: auto" v-if="messages.length">
                <div v-for="(message, index) in messages"
                     :key="index"
                     class="my-2"
                >
                  <div :class="message.user_id === $page.props.auth.user.id ? 'text-right' : 'text-left'">
                    <span class="inline-block px-3 py-1 rounded-lg"
                          :class="message.user_id === $page.props.auth.user.id ? 'bg-blue-500 text-white' : 'bg-green-300 text-black'">
                      {{ message.message }}
                    </span>
                  </div>
                </div>
              </div>
              <div style="height: 300px" v-else class="text-center">
                No messages yet
              </div>
              <!--  text field  -->
              <div>
                <input
                    v-model="inputMessage"
                    type="text"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-300 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="Your message"
                    required
                    autocomplete="off"
                    @keydown.enter="sendMessage"
                >
              </div>
            </div>
            <div v-else class="text-center w-1/2 mr-4 bg-white border border-gray-600 p-4 overflow-auto">
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
      inputMessage: '',
      chatSelected: false,
      selectedUser: null,
      chat: [],
    };
  },
  mounted() {
    this.getChats();
  },
  created() {
    // res.message instead of res.data

    // listen to user's channel
    // window.Echo.channel(`users.${this.$page.props.auth.user.id}`)
    //     .listen('.store-message', res => {
    //       console.log(res);
    //     })
  },
  methods: {
    sendMessage() {
      this.messages.push({
        user_id: this.$page.props.auth.user.id,
        chat_id: this.chat.id,
        message: this.inputMessage,
      })

      this.scrollToBottom();

      axios.post('/api/send-message', {
        chat_id: this.chat.id,
        message: this.inputMessage,
      })

      this.inputMessage = '';
    },
    getChats() {
      axios.get('/api/get-chats')
          .then(response => {
            this.users = response.data.users;
          })
          .catch(error => {
            console.error('Error fetching users:', error);
          });
    },
    startChat(id) {
      this.chatSelected = true;
      this.selectedUser = id;

      // stop listening to previous channel
      window.Echo.leave('store-message.' + this.chat.id)

      // make a call
      axios.post('/api/chats', {
        title: null,
        users: [id],
      }).then((response) => {
        this.chat = response.data
        this.messages = this.chat.messages

        this.scrollToBottom();

        // listen to upcoming messages
        Echo.channel('store-message.' + this.chat.id)
            .listen('.store-message', res => {
              this.messages.push({
                user_id: res.message.user_id,
                chat_id: res.message.chat_id,
                message: res.message.message,
              });
            })

        // listen to clearing chat
        Echo.channel('clear-chat.' + this.chat.id)
            .listen('.clear-chat', res => {
              this.messages = res.messages;
            })
      });
    },
    scrollToBottom() {
      this.$nextTick(() => {
        const chat = document.querySelector('.chat');
        if (chat) {
          chat.scrollTop = chat.scrollHeight;
        }
      });
    },
    clearChat() {
      axios.delete('/api/clear-chat/' + this.chat.id)
          .then(response => {
            this.messages = response.data
          })
    }
  }

}
</script>

<style scoped>

</style>