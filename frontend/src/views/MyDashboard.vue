<template>
  <div class="dashboard">
    <header class="header">
      <h1>Finance Dashboard</h1>
      <button @click="logout" class="logout-btn">
        <span>Logout</span>
      </button>
    </header>
    <div class="content">
      <!-- Add Transaction Section -->
      <div class="card add-section">
        <h2>Add New Transaction</h2>
        <form @submit.prevent="addTransaction" class="form">
          <div class="input-group">
            <input v-model="newTransaction.description" placeholder="Description" required />
            <input v-model.number="newTransaction.amount" type="number" step="0.01" placeholder="Amount" required />
            <input v-model="newTransaction.category" placeholder="Category" required />
            <select v-model="newTransaction.type" required>
              <option value="" disabled>Select Type</option>
              <option value="income">Income</option>
              <option value="expense">Expense</option>
            </select>
            <input v-model="newTransaction.date" type="date" required />
            <button type="submit" class="btn-primary">Add Transaction</button>
          </div>
        </form>
      </div>
      <!-- Transactions Table -->
      <div class="card">
        <h2>Transactions ({{ transactions.length }})</h2>
        <div class="table-container">
          <table v-if="transactions.length">
            <thead>
              <tr>
                <th>Description</th>
                <th>Amount</th>
                <th>Category</th>
                <th>Type</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="transaction in transactions" :key="transaction.id">
                <td>{{ transaction.description }}</td>
                <td>RM{{ transaction.amount }}</td>
                <td>{{ transaction.category }}</td>
                <td>{{ transaction.type }}</td>
                <td>{{ transaction.date }}</td>
                <td class="actions">
                  <button @click="startEdit(transaction)" :disabled="userRole !== 'admin'" class="btn-edit">
                    Edit
                  </button>
                  <button @click="confirmDelete(transaction.id)" :disabled="userRole !== 'admin'" class="btn-delete">
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="empty-state">
            <p>No transactions found</p>
          </div>
        </div>
      </div>
    </div>
    <!-- ...rest of template... -->
  </div>
</template>

<script>
export default {
  name: 'MyDashboard',
  data() {
    return {
      transactions: [],
      newTransaction: {
        description: '',
        amount: '',
        category: '',
        type: '',
        date: ''
      },
      editTransaction: null,
      userRole: 'admin', // or 'user', based on your auth logic
      errorMessage: '',
      successMessage: ''
    };
  },
  methods: {
    async fetchTransactions() {
      this.errorMessage = '';
      try {
        const token = localStorage.getItem('jwt_token');
        const res = await fetch('http://localhost:8000/api/transactions', {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();
        if (res.ok) {
          this.transactions = data;
        } else {
          this.errorMessage = data.error || 'Failed to fetch transactions.';
        }
      } catch {
        this.errorMessage = 'Network error. Please try again.';
      }
    },
    async addTransaction() {
      this.errorMessage = '';
      this.successMessage = '';
      try {
        const token = localStorage.getItem('jwt_token');
        const res = await fetch('http://localhost:8000/api/transactions', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify(this.newTransaction)
        });
        const data = await res.json();
        if (res.ok && data.id) {
          this.successMessage = 'Transaction added!';
          this.newTransaction = { description: '', amount: '', category: '', type: '', date: '' };
          await this.fetchTransactions();
        } else {
          this.errorMessage = data.error || 'Failed to add transaction.';
        }
      } catch (err) {
        this.errorMessage = 'Network error. Please try again.';
      }
    },
    logout() {
      localStorage.removeItem('jwt_token');
      localStorage.removeItem('user_info');
      this.$router.push('/');
    }
  },
  mounted() {
    this.fetchTransactions();
  }
};
</script>

<style scoped>
.dashboard {
  max-width: 900px;
  margin: 40px auto;
  padding: 32px;
  background: #fff;
  box-shadow: 0 6px 24px rgba(0,0,0,0.10);
  border-radius: 12px;
  border: 1px solid #e3e3e3;
  position: relative;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.logout-btn {
  padding: 8px 16px;
  background: #dc3545;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
.card {
  margin-top: 20px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e3e3e3;
}
.table-container {
  max-height: 400px;
  overflow-y: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}
th {
  background-color: #f1f1f1;
}
.input-group {
  display: flex;
  gap: 8px;
}
input, select {
  flex: 1;
  padding: 10px;
  border-radius: 4px;
  border: 1px solid #ccc;
}
button {
  padding: 10px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.btn-primary {
  background: #007bff;
  color: #fff;
}
.btn-edit {
  background: #ffc107;
  color: #fff;
}
.btn-delete {
  background: #dc3545;
  color: #fff;
}
.empty-state {
  text-align: center;
  padding: 20px;
  color: #999;
}
.error-msg, .success-msg {
  margin-top: 15px;
  padding: 10px;
  text-align: center;
  border-radius: 4px;
}
.error-msg {
  color: #dc3545;
  background: #fff0f2;
  border: 1px solid #f5c2c7;
}
.success-msg {
  color: #28a745;
  background: #eafaf1;
  border: 1px solid #b7e4c7;
}
</style>