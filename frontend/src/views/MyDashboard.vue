<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <h1>Finance Dashboard</h1>
      <button @click="logout" class="logout-btn">
        <span>Logout</span>
      </button>
    </div>

    <div class="dashboard-content">
      <!-- Add Transaction Section -->
      <div class="card add-transaction-card">
        <div class="card-header">
          <h3>Add New Transaction</h3>
        </div>
        <form @submit.prevent="addTransaction" class="transaction-form">
          <div class="form-row">
            <div class="form-group">
              <label>Description</label>
              <input 
                v-model="newTransaction.description" 
                placeholder="Enter description" 
                required 
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>Amount (RM)</label>
              <input 
                v-model.number="newTransaction.amount" 
                type="number" 
                step="0.01" 
                placeholder="0.00" 
                required 
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>Category</label>
              <input 
                v-model="newTransaction.category" 
                placeholder="Enter category" 
                required 
                class="form-input"
              />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Type</label>
              <select v-model="newTransaction.type" required class="form-select">
                <option value="" disabled>Select Type</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
              </select>
            </div>
            <div class="form-group">
              <label>Date</label>
              <input 
                v-model="newTransaction.date" 
                type="date" 
                required 
                class="form-input"
              />
            </div>
            <div class="form-group form-actions">
              <button type="submit" class="submit-btn">Add Transaction</button>
            </div>
          </div>
        </form>
        
        <!-- Messages -->
        <div v-if="errorMessage" class="message error-message">
          {{ errorMessage }}
        </div>
        <div v-if="successMessage" class="message success-message">
          {{ successMessage }}
        </div>
      </div>

      <!-- Transactions Section -->
      <div class="card transactions-card">
        <div class="card-header">
          <h3>Transactions</h3>
          <div class="transaction-count">
            {{ transactions.length }} transaction{{ transactions.length !== 1 ? 's' : '' }}
          </div>
        </div>
        
        <div class="transactions-container">
          <div v-if="transactions.length === 0" class="empty-state">
            <div class="empty-icon">📊</div>
            <p>No transactions yet</p>
            <span>Add your first transaction above</span>
          </div>
          
          <div v-else class="transactions-list">
            <div class="transaction-item" v-for="transaction in transactions" :key="transaction.id">
              <div class="transaction-content">
                <div class="transaction-main">
                  <div class="transaction-description">
                    <h4>{{ transaction.description }}</h4>
                    <span class="transaction-category">{{ transaction.category }}</span>
                  </div>
                  <div class="transaction-amount" :class="{ 'income': transaction.type === 'income' }">
                    RM{{ formatAmount(transaction.amount) }}
                  </div>
                </div>
                <div class="transaction-meta">
                  <span class="transaction-type" :class="transaction.type">
                    {{ transaction.type }}
                  </span>
                  <span class="transaction-date">{{ formatDate(transaction.date) }}</span>
                  <div class="transaction-actions">
                    <button 
                      @click="startEdit(transaction)" 
                      :disabled="userRole !== 'admin'" 
                      class="action-btn edit-btn"
                    >
                      Edit
                    </button>
                    <button 
                      @click="confirmDelete(transaction.id)" 
                      :disabled="userRole !== 'admin'" 
                      class="action-btn delete-btn"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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
          // Ensure amounts are properly converted to numbers
          this.transactions = data.map(transaction => ({
            ...transaction,
            amount: parseFloat(transaction.amount) || 0
          }));
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
          this.successMessage = 'Transaction added successfully!';
          this.newTransaction = { description: '', amount: '', category: '', type: '', date: '' };
          await this.fetchTransactions();
          setTimeout(() => {
            this.successMessage = '';
          }, 3000);
        } else {
          this.errorMessage = data.error || 'Failed to add transaction.';
        }
      } catch (err) {
        this.errorMessage = 'Network error. Please try again.';
      }
    },
    formatAmount(amount) {
      // Helper method to safely format amounts
      const numAmount = parseFloat(amount);
      return isNaN(numAmount) ? '0.00' : numAmount.toFixed(2);
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    startEdit(transaction) {
      console.log('Edit transaction:', transaction);
      // Add your edit logic here
    },
    confirmDelete(transactionId) {
      if (confirm('Are you sure you want to delete this transaction?')) {
        console.log('Delete transaction:', transactionId);
        // Add your delete logic here
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
* {
  box-sizing: border-box;
}

.dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.dashboard-header h1 {
  color: white;
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.logout-btn {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.logout-btn:hover {
  background: #c0392b;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
}

.card-header {
  background: #f8f9fa;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.25rem;
  font-weight: 600;
}

.transaction-count {
  color: #6c757d;
  font-size: 0.875rem;
  font-weight: 500;
}

.add-transaction-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.transaction-form {
  padding: 2rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 0.5rem;
  color: #495057;
  font-weight: 600;
  font-size: 0.875rem;
}

.form-input,
.form-select {
  padding: 0.75rem 1rem;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: white;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions {
  display: flex;
  align-items: flex-end;
}

.submit-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  width: 100%;
}

.submit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.message {
  margin-top: 1rem;
  padding: 1rem;
  border-radius: 8px;
  font-weight: 500;
  text-align: center;
}

.error-message {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.success-message {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.transactions-container {
  padding: 2rem;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #6c757d;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.empty-state p {
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
  color: #495057;
}

.transactions-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.transaction-item {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  border: 1px solid #e9ecef;
}

.transaction-item:hover {
  background: #e9ecef;
  transform: translateX(5px);
}

.transaction-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.transaction-main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.transaction-description h4 {
  margin: 0 0 0.25rem 0;
  color: #2c3e50;
  font-size: 1.125rem;
}

.transaction-category {
  color: #6c757d;
  font-size: 0.875rem;
  background: #e9ecef;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
}

.transaction-amount {
  font-size: 1.25rem;
  font-weight: 700;
  color: #e74c3c;
}

.transaction-amount.income {
  color: #27ae60;
}

.transaction-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.transaction-type {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.transaction-type.income {
  background: #d4edda;
  color: #155724;
}

.transaction-type.expense {
  background: #f8d7da;
  color: #721c24;
}

.transaction-date {
  color: #6c757d;
  font-size: 0.875rem;
}

.transaction-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.edit-btn {
  background: #ffc107;
  color: #212529;
}

.edit-btn:hover:not(:disabled) {
  background: #e0a800;
  transform: translateY(-1px);
}

.delete-btn {
  background: #dc3545;
  color: white;
}

.delete-btn:hover:not(:disabled) {
  background: #c82333;
  transform: translateY(-1px);
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 768px) {
  .dashboard {
    padding: 1rem;
  }
  
  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .dashboard-header h1 {
    font-size: 2rem;
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .transaction-main {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .transaction-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .transaction-actions {
    align-self: flex-end;
  }
}

@media (max-width: 480px) {
  .card-header {
    padding: 1rem;
  }
  
  .transaction-form {
    padding: 1rem;
  }
  
  .transactions-container {
    padding: 1rem;
  }
  
  .transaction-item {
    padding: 1rem;
  }
}
</style>