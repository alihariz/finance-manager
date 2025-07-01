import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const store = new Vuex.Store({
  state: {
    users: [],
    transactions: [],
  },
  mutations: {
    setUsers(state, users) {
      state.users = users;
    },
    setTransactions(state, transactions) {
      state.transactions = transactions;
    },
  },
  actions: {
    fetchUsers({ commit }) {
      // Fetch users data from API and commit to state
    },
    async fetchTransactions({ commit }) {
      const res = await fetch('http://localhost:8000/api/transactions', {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
      });
      const data = await res.json();
      commit('setTransactions', data);
    },
  },
  getters: {
    getUsers: state => state.users,
    getTransactions: state => state.transactions,
  },
});

export default store;