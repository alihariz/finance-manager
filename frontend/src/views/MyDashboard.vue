<template>
  <div class="dashboard">
    <div class="sidebar">
      <div class="sidebar-title">Finance Manager</div>
      <router-link to="/dashboard" class="sidebar-link" active-class="active-link">
        Dashboard
      </router-link>
      <router-link to="/statistics" class="sidebar-link" active-class="active-link">
        Statistics
      </router-link>
      <button @click="logout" class="sidebar-link logout-btn">
        Logout
      </button>
    </div>
    <div class="dashboard-main">
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
              <label>Type</label>
              <select v-model="newTransaction.type" required class="form-select" @change="onTypeChange">
                <option value="" disabled>Select Type</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Category</label>
              <select v-model="newTransaction.category" required class="form-select">
                <option value="" disabled>Select Category</option>
                <option v-for="category in availableCategories" :key="category" :value="category">
                  {{ category }}
                </option>
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

      <!-- Filter Section -->
      <div class="card filter-card">
        <div class="card-header">
          <h3>Filter Transactions</h3>
          <button @click="clearFilters" class="clear-filters-btn">
            Clear Filters
          </button>
        </div>
        <div class="filter-container">
          <div class="filter-row">
            <div class="filter-group">
              <label>Transaction Type</label>
              <select v-model="filters.type" class="form-select" @change="applyFilters">
                <option value="">All Types</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
              </select>
            </div>
            <div class="filter-group">
              <label>Category</label>
              <select v-model="filters.category" class="form-select" @change="applyFilters">
                <option value="">All Categories</option>
                <option v-for="category in allCategories" :key="category" :value="category">
                  {{ category }}
                </option>
              </select>
            </div>
            <div class="filter-group">
              <label>Time Period</label>
              <select v-model="filters.timePeriod" class="form-select" @change="applyFilters">
                <option value="">All Time</option>
                <option value="thisWeek">This Week</option>
                <option value="thisMonth">This Month</option>
                <option value="lastMonth">Last Month</option>
                <option value="thisYear">This Year</option>
                <option value="lastYear">Last Year</option>
                <option value="custom">Custom Range</option>
              </select>
            </div>
          </div>
          
          <!-- Custom Date Range -->
          <div v-if="filters.timePeriod === 'custom'" class="filter-row custom-date-row">
            <div class="filter-group">
              <label>From Date</label>
              <input 
                v-model="filters.startDate" 
                type="date" 
                class="form-input"
                @change="applyFilters"
              />
            </div>
            <div class="filter-group">
              <label>To Date</label>
              <input 
                v-model="filters.endDate" 
                type="date" 
                class="form-input"
                @change="applyFilters"
              />
            </div>
            <div class="filter-group">
              <label>Amount Range</label>
              <div class="amount-range">
                <input 
                  v-model.number="filters.minAmount" 
                  type="number" 
                  step="0.01" 
                  placeholder="Min"
                  class="form-input amount-input"
                  @input="applyFilters"
                />
                <span class="range-separator">-</span>
                <input 
                  v-model.number="filters.maxAmount" 
                  type="number" 
                  step="0.01" 
                  placeholder="Max"
                  class="form-input amount-input"
                  @input="applyFilters"
                />
              </div>
            </div>
          </div>
          
          <!-- Filter Summary -->
          <div class="filter-summary">
            <div class="filter-stats">
              <span class="stat-item">
                <strong>{{ filteredTransactions.length }}</strong> 
                {{ filteredTransactions.length === 1 ? 'transaction' : 'transactions' }}
              </span>
              <span class="stat-item income">
                Income: <strong>RM{{ formatAmount(totalIncome) }}</strong>
              </span>
              <span class="stat-item expense">
                Expense: <strong>RM{{ formatAmount(totalExpense) }}</strong>
              </span>
              <span class="stat-item net" :class="{ 'positive': netAmount >= 0, 'negative': netAmount < 0 }">
                Net: <strong>{{ netAmount >= 0 ? '+' : '' }}RM{{ formatAmount(netAmount) }}</strong>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Transactions Section -->
      <div class="card transactions-card">
        <div class="card-header">
          <h3>Transactions</h3>
          <div class="transaction-count">
            {{ filteredTransactions.length }} of {{ transactions.length }} transaction{{ transactions.length !== 1 ? 's' : '' }}
          </div>
        </div>
        
        <div class="transactions-container">
          <div v-if="filteredTransactions.length === 0" class="empty-state">
            <div class="empty-icon">📊</div>
            <p v-if="transactions.length === 0">No transactions yet</p>
            <p v-else>No transactions match your filters</p>
            <span v-if="transactions.length === 0">Add your first transaction above</span>
            <span v-else>Try adjusting your filter criteria</span>
          </div>
          
          <div v-else class="transactions-list">
            <div class="transaction-item" v-for="transaction in filteredTransactions" :key="transaction.id">
              <div class="transaction-content">
                <div class="transaction-main">
                  <div class="transaction-left">
                    <div class="transaction-description">
                      <h4>{{ transaction.description }}</h4>
                    </div>
                    <div class="transaction-details">
                      <span class="transaction-category">{{ transaction.category }}</span>
                      <span class="transaction-type" :class="transaction.type">
                        {{ transaction.type }}
                      </span>
                    </div>
                  </div>
                  <div class="transaction-right">
                    <div class="transaction-amount" :class="{ 'income': transaction.type === 'income' }">
                      {{ transaction.type === 'income' ? '+' : '-' }}RM{{ formatAmount(transaction.amount) }}
                    </div>
                    <div class="transaction-date">{{ formatDate(transaction.date) }}</div>
                  </div>
                </div>
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

      <!-- Edit Transaction Modal -->
      <div v-if="showEditModal" class="modal-overlay">
        <div class="modal-content">
          <h3>Edit Transaction</h3>
          <form @submit.prevent="submitEdit" class="transaction-form">
            <div class="form-row">
              <div class="form-group">
                <label>Description</label>
                <input v-model="editTransaction.description" required class="form-input" />
              </div>
              <div class="form-group">
                <label>Amount (RM)</label>
                <input v-model.number="editTransaction.amount" type="number" step="0.01" required class="form-input" />
              </div>
              <div class="form-group">
                <label>Type</label>
                <select v-model="editTransaction.type" required class="form-select" @change="onEditTypeChange">
                  <option value="income">Income</option>
                  <option value="expense">Expense</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Category</label>
                <select v-model="editTransaction.category" required class="form-select">
                  <option value="" disabled>Select Category</option>
                  <option v-for="category in getEditCategories()" :key="category" :value="category">
                    {{ category }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label>Date</label>
                <input v-model="editTransaction.date" type="date" required class="form-input" />
              </div>
              <div class="form-group form-actions">
                <button type="submit" class="submit-btn">Save</button>
                <button type="button" @click="closeEditModal" class="submit-btn" style="background:#dc3545;color:white;">Cancel</button>
              </div>
            </div>
          </form>
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
      filteredTransactions: [],
      newTransaction: {
        description: '',
        amount: '',
        category: '',
        type: '',
        date: ''
      },
      editTransaction: null,
      userRole: 'admin',
      errorMessage: '',
      successMessage: '',
      showEditModal: false,
      filters: {
        type: '',
        category: '',
        timePeriod: '',
        startDate: '',
        endDate: '',
        minAmount: '',
        maxAmount: ''
      },
      incomeCategories: [
        'Salary',
        'Freelance',
        'Business',
        'Investment',
        'Allowance',
        'Bonus',
        'Gift',
        'Rental Income',
        'Dividends',
        'Interest',
        'Other Income'
      ],
      expenseCategories: [
        'Food & Dining',
        'Groceries',
        'Transportation',
        'Shopping',
        'Entertainment',
        'Bills & Utilities',
        'Healthcare',
        'Education',
        'Travel',
        'Personal Care',
        'Home & Garden',
        'Insurance',
        'Taxes',
        'Investment',
        'Charity',
        'Other Expenses'
      ]
    };
  },
  computed: {
    availableCategories() {
      if (this.newTransaction.type === 'income') {
        return this.incomeCategories;
      } else if (this.newTransaction.type === 'expense') {
        return this.expenseCategories;
      }
      return [];
    },
    allCategories() {
      return [...this.incomeCategories, ...this.expenseCategories].sort();
    },
    totalIncome() {
      return this.filteredTransactions
        .filter(t => t.type === 'income')
        .reduce((sum, t) => sum + parseFloat(t.amount), 0);
    },
    totalExpense() {
      return this.filteredTransactions
        .filter(t => t.type === 'expense')
        .reduce((sum, t) => sum + parseFloat(t.amount), 0);
    },
    netAmount() {
      return this.totalIncome - this.totalExpense;
    }
  },
  methods: {
    onTypeChange() {
      this.newTransaction.category = '';
    },
    onEditTypeChange() {
      this.editTransaction.category = '';
    },
    getEditCategories() {
      if (!this.editTransaction) return [];
      if (this.editTransaction.type === 'income') {
        return this.incomeCategories;
      } else if (this.editTransaction.type === 'expense') {
        return this.expenseCategories;
      }
      return [];
    },
    async fetchTransactions() {
      this.errorMessage = '';
      try {
        const token = localStorage.getItem('jwt_token');
        const res = await fetch('http://localhost:8000/api/transactions', {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();
        if (res.ok) {
          this.transactions = data.map(transaction => ({
            ...transaction,
            amount: parseFloat(transaction.amount) || 0
          }));
          this.applyFilters();
        } else {
          this.errorMessage = data.error || 'Failed to fetch transactions.';
        }
      } catch {
        this.errorMessage = 'Network error. Please try again.';
      }
    },
    applyFilters() {
      let filtered = [...this.transactions];

      // Filter by type
      if (this.filters.type) {
        filtered = filtered.filter(t => t.type === this.filters.type);
      }

      // Filter by category
      if (this.filters.category) {
        filtered = filtered.filter(t => t.category === this.filters.category);
      }

      // Filter by time period
      if (this.filters.timePeriod) {
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        
        switch (this.filters.timePeriod) {
          case 'thisWeek':
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay());
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            filtered = filtered.filter(t => {
              const date = new Date(t.date);
              return date >= weekStart && date <= weekEnd;
            });
            break;
            
          case 'thisMonth':
            const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
            const monthEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            filtered = filtered.filter(t => {
              const date = new Date(t.date);
              return date >= monthStart && date <= monthEnd;
            });
            break;
            
          case 'lastMonth':
            const lastMonthStart = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            const lastMonthEnd = new Date(now.getFullYear(), now.getMonth(), 0);
            filtered = filtered.filter(t => {
              const date = new Date(t.date);
              return date >= lastMonthStart && date <= lastMonthEnd;
            });
            break;
            
          case 'thisYear':
            const yearStart = new Date(now.getFullYear(), 0, 1);
            const yearEnd = new Date(now.getFullYear(), 11, 31);
            filtered = filtered.filter(t => {
              const date = new Date(t.date);
              return date >= yearStart && date <= yearEnd;
            });
            break;
            
          case 'lastYear':
            const lastYearStart = new Date(now.getFullYear() - 1, 0, 1);
            const lastYearEnd = new Date(now.getFullYear() - 1, 11, 31);
            filtered = filtered.filter(t => {
              const date = new Date(t.date);
              return date >= lastYearStart && date <= lastYearEnd;
            });
            break;
            
          case 'custom':
            if (this.filters.startDate) {
              filtered = filtered.filter(t => new Date(t.date) >= new Date(this.filters.startDate));
            }
            if (this.filters.endDate) {
              filtered = filtered.filter(t => new Date(t.date) <= new Date(this.filters.endDate));
            }
            break;
        }
      }

      // Filter by amount range (for custom period)
      if (this.filters.minAmount !== '' && this.filters.minAmount !== null) {
        filtered = filtered.filter(t => parseFloat(t.amount) >= parseFloat(this.filters.minAmount));
      }
      if (this.filters.maxAmount !== '' && this.filters.maxAmount !== null) {
        filtered = filtered.filter(t => parseFloat(t.amount) <= parseFloat(this.filters.maxAmount));
      }

      // Sort by date (newest first)
      filtered.sort((a, b) => new Date(b.date) - new Date(a.date));

      this.filteredTransactions = filtered;
    },
    clearFilters() {
      this.filters = {
        type: '',
        category: '',
        timePeriod: '',
        startDate: '',
        endDate: '',
        minAmount: '',
        maxAmount: ''
      };
      this.applyFilters();
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
      this.editTransaction = { ...transaction };
      this.showEditModal = true;
      this.errorMessage = '';
      this.successMessage = '';
    },
    async submitEdit() {
      this.errorMessage = '';
      this.successMessage = '';
      try {
        const token = localStorage.getItem('jwt_token');
        const res = await fetch(`http://localhost:8000/api/transactions/${this.editTransaction.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify(this.editTransaction)
        });
        const data = await res.json();
        if (res.ok) {
          this.successMessage = 'Transaction updated successfully!';
          this.showEditModal = false;
          this.editTransaction = null;
          await this.fetchTransactions();
          setTimeout(() => { this.successMessage = ''; }, 3000);
        } else {
          this.errorMessage = data.error || 'Failed to update transaction.';
        }
      } catch {
        this.errorMessage = 'Network error. Please try again.';
      }
    },
    closeEditModal() {
      this.showEditModal = false;
      this.editTransaction = null;
    },
    async confirmDelete(transactionId) {
      if (!confirm('Are you sure you want to delete this transaction?')) return;
      this.errorMessage = '';
      this.successMessage = '';
      try {
        const token = localStorage.getItem('jwt_token');
        const res = await fetch(`http://localhost:8000/api/transactions/${transactionId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        const data = await res.json();
        if (res.ok) {
          this.successMessage = 'Transaction deleted successfully!';
          await this.fetchTransactions();
          setTimeout(() => { this.successMessage = ''; }, 3000);
        } else {
          this.errorMessage = data.error || 'Failed to delete transaction.';
        }
      } catch {
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
* {
  box-sizing: border-box;
}

.dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.sidebar {
  width: 220px;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 2rem 1rem;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 10;
}

.sidebar-title {
  color: #fff;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 2rem;
  letter-spacing: 1px;
}

.sidebar-link {
  color: #fff;
  background: none;
  border: none;
  font-size: 1.1rem;
  font-weight: 500;
  padding: 0.75rem 1rem;
  margin-bottom: 0.5rem;
  border-radius: 8px;
  text-align: left;
  width: 100%;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.2s;
  display: block;
}

.sidebar-link:hover,
.sidebar-link.active-link {
  background: rgba(255,255,255,0.15);
}

.logout-btn {
  margin-top: auto;
  background: #e74c3c;
  color: #fff;
}

.logout-btn:hover {
  background: #c0392b;
}

.dashboard-main {
  margin-left: 240px;
  padding: 2rem 0 2rem 0;
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

.clear-filters-btn {
  background: #6c757d;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.clear-filters-btn:hover {
  background: #5a6268;
  transform: translateY(-1px);
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

/* Filter Styles */
.filter-card {
  background: linear-gradient(135deg, #e8f5e8 0%, #f0f8ff 100%);
}

.filter-container {
  padding: 2rem;
}

.filter-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.custom-date-row {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-group label {
  margin-bottom: 0.5rem;
  color: #495057;
  font-weight: 600;
  font-size: 0.875rem;
}

.amount-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.amount-input {
  flex: 1;
}

.range-separator {
  color: #6c757d;
  font-weight: 500;
}

.filter-summary {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #e9ecef;
  margin-top: 1rem;
}

.filter-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  text-align: center;
}

.stat-item {
  padding: 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  background: white;
  border: 1px solid #e9ecef;
}

.stat-item.income {
  background: #d4edda;
  color: #155724;
  border-color: #c3e6cb;
}

.stat-item.expense {
  background: #f8d7da;
  color: #721c24;
  border-color: #f5c6cb;
}

.stat-item.net.positive {
  background: #d4edda;
  color: #155724;
  border-color: #c3e6cb;
}

.stat-item.net.negative {
  background: #f8d7da;
  color: #721c24;
  border-color: #f5c6cb;
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
  align-items: center;
}

.transaction-left {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
}

.transaction-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.25rem;
}

.transaction-description h4 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.125rem;
  font-weight: 600;
}

.transaction-details {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.transaction-category {
  color: #6c757d;
  font-size: 0.875rem;
  background: #e9ecef;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-weight: 500;
}

.transaction-amount {
  font-size: 1.25rem;
  font-weight: 700;
  color: #e74c3c;
}

.transaction-amount.income {
  color: #27ae60;
}

.transaction-date {
  color: #6c757d;
  font-size: 0.875rem;
  font-weight: 500;
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

.transaction-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
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

.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(44, 62, 80, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  border-radius: 12px;
  padding: 2rem;
  min-width: 500px;
  max-width: 90vw;
  box-shadow: 0 8px 32px rgba(44, 62, 80, 0.2);
}

.modal-content h3 {
  margin-bottom: 1.5rem;
  color: #764ba2;
  text-align: center;
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
  
  .form-row,
  .filter-row {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .filter-stats {
    grid-template-columns: 1fr;
  }
  
  .transaction-main {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .transaction-right {
    align-items: flex-start;
    width: 100%;
  }
  
  .transaction-actions {
    justify-content: flex-start;
    margin-top: 0.5rem;
  }
  
  .modal-content {
    min-width: 350px;
  }
}

@media (max-width: 480px) {
  .card-header {
    padding: 1rem;
    flex-direction: column;
    gap: 0.5rem;
    text-align: center;
  }
  
  .transaction-form,
  .filter-container {
    padding: 1rem;
  }
  
  .transactions-container {
    padding: 1rem;
  }
  
  .transaction-item {
    padding: 1rem;
  }
  
  .transaction-details {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .amount-range {
    flex-direction: column;
    align-items: stretch;
  }
  
  .range-separator {
    text-align: center;
  }
}
</style>
