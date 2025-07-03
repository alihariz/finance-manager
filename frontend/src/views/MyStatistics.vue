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
    <div class="statistics-view">
      <h1>Statistics</h1>
      
      <!-- Filter Controls -->
      <div class="filter-controls">
        <div class="filter-group">
          <label class="filter-label">View Type:</label>
          <div class="filter-buttons">
            <button 
              :class="['filter-btn', selectedView === 'category' ? 'active' : '']" 
              @click="selectedView = 'category'"
            >
              By Category
            </button>
            <button 
              :class="['filter-btn', selectedView === 'time' ? 'active' : '']" 
              @click="selectedView = 'time'"
            >
              Time Analysis
            </button>
            <button 
              :class="['filter-btn', selectedView === 'comparison' ? 'active' : '']" 
              @click="selectedView = 'comparison'"
            >
              Comparison
            </button>
          </div>
        </div>
        
        <div class="filter-group" v-if="selectedView === 'time'">
          <label class="filter-label">Time Period:</label>
          <div class="filter-buttons">
            <button 
              :class="['filter-btn', timePeriod === 'weekly' ? 'active' : '']" 
              @click="timePeriod = 'weekly'"
            >
              Weekly
            </button>
            <button 
              :class="['filter-btn', timePeriod === 'monthly' ? 'active' : '']" 
              @click="timePeriod = 'monthly'"
            >
              Monthly
            </button>
            <button 
              :class="['filter-btn', timePeriod === 'quarterly' ? 'active' : '']" 
              @click="timePeriod = 'quarterly'"
            >
              Quarterly
            </button>
            <button 
              :class="['filter-btn', timePeriod === 'yearly' ? 'active' : '']" 
              @click="timePeriod = 'yearly'"
            >
              Yearly
            </button>
          </div>
        </div>
        
        <div class="filter-group" v-if="selectedView === 'comparison'">
          <label class="filter-label">Compare:</label>
          <div class="filter-buttons">
            <button 
              :class="['filter-btn', comparisonType === 'month' ? 'active' : '']" 
              @click="comparisonType = 'month'"
            >
              Month vs Month
            </button>
            <button 
              :class="['filter-btn', comparisonType === 'quarter' ? 'active' : '']" 
              @click="comparisonType = 'quarter'"
            >
              Quarter vs Quarter
            </button>
            <button 
              :class="['filter-btn', comparisonType === 'year' ? 'active' : '']" 
              @click="comparisonType = 'year'"
            >
              Year vs Year
            </button>
          </div>
        </div>
        
        <!-- Date Range Filter -->
        <div class="filter-group">
          <label class="filter-label">Date Range:</label>
          <div class="date-range-controls">
            <input 
              type="date" 
              v-model="startDate" 
              class="date-input"
              @change="filterTransactionsByDate"
            >
            <span class="date-separator">to</span>
            <input 
              type="date" 
              v-model="endDate" 
              class="date-input"
              @change="filterTransactionsByDate"
            >
            <button @click="resetDateRange" class="reset-btn">Reset</button>
          </div>
        </div>
      </div>
      
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>Loading statistics...</p>
      </div>
      
      <div v-else-if="errorMessage" class="error-state">
        <div class="error-icon">⚠️</div>
        <p>{{ errorMessage }}</p>
        <button @click="fetchTransactions" class="retry-btn">Retry</button>
      </div>
      
      <div v-else class="charts-container">
        <!-- Main Chart -->
        <div class="card chart-card main-chart">
          <h3>{{ getChartTitle() }}</h3>
          <div class="chart-wrapper">
            <div v-if="hasMainChartData" class="chart-container">
              <component 
                :is="getMainChartComponent()" 
                :data="getMainChartData()" 
                :options="getMainChartOptions()" 
              />
            </div>
            <div v-else class="no-data">
              <div class="no-data-icon">📊</div>
              <p>No data available for selected period</p>
            </div>
          </div>
        </div>
        
        <!-- Secondary Chart -->
        <div class="card chart-card secondary-chart">
          <h3>{{ getSecondaryChartTitle() }}</h3>
          <div class="chart-wrapper">
            <div v-if="hasSecondaryChartData" class="chart-container">
              <component 
                :is="getSecondaryChartComponent()" 
                :data="getSecondaryChartData()" 
                :options="getSecondaryChartOptions()" 
              />
            </div>
            <div v-else class="no-data">
              <div class="no-data-icon">📈</div>
              <p>No secondary data available</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Summary Statistics -->
      <div class="card stats-summary">
        <h3>Summary ({{ getDateRangeText() }})</h3>
        <div class="summary-items">
          <div class="summary-item">
            <h4>Total Income</h4>
            <p class="summary-value income">{{ formatCurrency(getTotalIncome()) }}</p>
          </div>
          <div class="summary-item">
            <h4>Total Expenses</h4>
            <p class="summary-value expense">{{ formatCurrency(getTotalExpenses()) }}</p>
          </div>
          <div class="summary-item">
            <h4>Net Balance</h4>
            <p class="summary-value" :class="{ 'positive': getNetBalance() >= 0, 'negative': getNetBalance() < 0 }">
              {{ formatCurrency(getNetBalance()) }}
            </p>
          </div>
          <div class="summary-item">
            <h4>Total Transactions</h4>
            <p class="summary-value">{{ filteredTransactions.length }}</p>
          </div>
          <div class="summary-item">
            <h4>Average per Transaction</h4>
            <p class="summary-value">{{ formatCurrency(getAverage()) }}</p>
          </div>
          <div class="summary-item">
            <h4>Highest Expense</h4>
            <p class="summary-value">{{ formatCurrency(getHighestExpense()) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title, LineElement, PointElement } from 'chart.js'
import { Pie, Bar, Line } from 'vue-chartjs'

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title, LineElement, PointElement)

export default {
  name: 'MyStatistics',
  components: {
    Pie,
    Bar,
    Line
  },
  data() {
    return {
      transactions: [],
      filteredTransactions: [],
      selectedView: 'category',
      timePeriod: 'monthly',
      comparisonType: 'month',
      startDate: '',
      endDate: '',
      loading: true,
      errorMessage: '',
      incomeCategories: [
        'Salary', 'Freelance', 'Business', 'Investment', 'Allowance', 'Bonus', 
        'Gift', 'Rental Income', 'Dividends', 'Interest', 'Other Income'
      ],
      expenseCategories: [
        'Food & Dining', 'Groceries', 'Transportation', 'Shopping', 'Entertainment', 
        'Bills & Utilities', 'Healthcare', 'Education', 'Travel', 'Personal Care', 
        'Home & Garden', 'Insurance', 'Taxes', 'Investment', 'Charity', 'Other Expenses'
      ]
    }
  },
  computed: {
    hasMainChartData() {
      const data = this.getMainChartData()
      return data && data.datasets && data.datasets[0] && data.datasets[0].data.length > 0
    },
    hasSecondaryChartData() {
      const data = this.getSecondaryChartData()
      return data && data.datasets && data.datasets[0] && data.datasets[0].data.some(val => val > 0)
    },
    
    // Category-based computations
    categoryTotals() {
      const totals = {}
      this.filteredTransactions
        .filter(t => t.type === 'expense')
        .forEach(transaction => {
          if (!totals[transaction.category]) {
            totals[transaction.category] = 0
          }
          totals[transaction.category] += parseFloat(transaction.amount)
        })
      return totals
    },
    
    // Time-based computations
    weeklyTotals() {
      const weeks = {}
      this.filteredTransactions
        .filter(t => t.type === 'expense')
        .forEach(transaction => {
          const date = new Date(transaction.date)
          const weekStart = new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay())
          const weekKey = `${weekStart.getMonth() + 1}/${weekStart.getDate()}`
          
          if (!weeks[weekKey]) weeks[weekKey] = 0
          weeks[weekKey] += parseFloat(transaction.amount)
        })
      return weeks
    },
    
    monthlyTotals() {
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                     'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
      const totals = {}
      months.forEach((month, idx) => {
        totals[month] = this.filteredTransactions
          .filter(t => t.type === 'expense' && new Date(t.date).getMonth() === idx)
          .reduce((sum, t) => sum + parseFloat(t.amount), 0)
      })
      return totals
    },
    
    quarterlyTotals() {
      const quarters = ['Q1', 'Q2', 'Q3', 'Q4']
      const totals = {}
      quarters.forEach((quarter, idx) => {
        totals[quarter] = this.filteredTransactions
          .filter(t => {
            const month = new Date(t.date).getMonth()
            const quarterIndex = Math.floor(month / 3)
            return t.type === 'expense' && quarterIndex === idx
          })
          .reduce((sum, t) => sum + parseFloat(t.amount), 0)
      })
      return totals
    },
    
    yearlyTotals() {
      const years = {}
      this.filteredTransactions
        .filter(t => t.type === 'expense')
        .forEach(transaction => {
          const year = new Date(transaction.date).getFullYear()
          if (!years[year]) years[year] = 0
          years[year] += parseFloat(transaction.amount)
        })
      return years
    }
  },
  methods: {
    async fetchTransactions() {
      this.loading = true
      this.errorMessage = ''
      try {
        const token = localStorage.getItem('jwt_token')
        const res = await fetch('https://finance-manager-webtect.duckdns.org/api/transactions', {
          headers: { 'Authorization': `Bearer ${token}` }
        })
        const data = await res.json()
        if (res.ok) {
          this.transactions = data.map(transaction => ({
            ...transaction,
            amount: parseFloat(transaction.amount) || 0
          }))
          this.filteredTransactions = [...this.transactions]
          this.setDefaultDateRange()
        } else {
          this.errorMessage = data.error || 'Failed to fetch transactions.'
        }
      } catch (error) {
        this.errorMessage = 'Network error. Please try again.'
      } finally {
        this.loading = false
      }
    },
    
    setDefaultDateRange() {
      if (this.transactions.length > 0) {
        const dates = this.transactions.map(t => new Date(t.date)).sort((a, b) => a - b)
        this.startDate = dates[0].toISOString().split('T')[0]
        this.endDate = dates[dates.length - 1].toISOString().split('T')[0]
      }
    },
    
    filterTransactionsByDate() {
      if (!this.startDate || !this.endDate) {
        this.filteredTransactions = [...this.transactions]
        return
      }
      
      const start = new Date(this.startDate)
      const end = new Date(this.endDate)
      
      this.filteredTransactions = this.transactions.filter(transaction => {
        const transactionDate = new Date(transaction.date)
        return transactionDate >= start && transactionDate <= end
      })
    },
    
    resetDateRange() {
      this.startDate = ''
      this.endDate = ''
      this.filteredTransactions = [...this.transactions]
    },
    
    getDateRangeText() {
      if (!this.startDate || !this.endDate) return 'All Time'
      return `${this.startDate} to ${this.endDate}`
    },
    
    // Chart title methods
    getChartTitle() {
      if (this.selectedView === 'category') {
        return 'Spending by Category'
      } else if (this.selectedView === 'time') {
        return `${this.timePeriod.charAt(0).toUpperCase() + this.timePeriod.slice(1)} Spending Trend`
      } else if (this.selectedView === 'comparison') {
        return `${this.comparisonType.charAt(0).toUpperCase() + this.comparisonType.slice(1)} Comparison`
      }
      return 'Financial Overview'
    },
    
    getSecondaryChartTitle() {
      if (this.selectedView === 'category') {
        return 'Income vs Expenses'
      } else if (this.selectedView === 'time') {
        return 'Monthly Overview'
      } else if (this.selectedView === 'comparison') {
        return 'Income Comparison'
      }
      return 'Secondary Analysis'
    },
    
    // Chart component methods
    getMainChartComponent() {
      if (this.selectedView === 'category') return 'Pie'
      if (this.selectedView === 'time') return 'Line'
      if (this.selectedView === 'comparison') return 'Bar'
      return 'Bar'
    },
    
    getSecondaryChartComponent() {
      if (this.selectedView === 'category') return 'Pie'
      if (this.selectedView === 'time') return 'Bar'
      if (this.selectedView === 'comparison') return 'Line'
      return 'Bar'
    },
    
    // Chart data methods
    getMainChartData() {
      if (this.selectedView === 'category') {
        return {
          labels: Object.keys(this.categoryTotals),
          datasets: [{
            data: Object.values(this.categoryTotals),
            backgroundColor: [
              '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', 
              '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384',
              '#E7E9ED', '#71B37C', '#518BC4', '#FFA07A', '#87CEEB', '#DDA0DD'
            ],
            borderWidth: 2,
            borderColor: '#fff'
          }]
        }
      } else if (this.selectedView === 'time') {
        let labels, data
        if (this.timePeriod === 'weekly') {
          labels = Object.keys(this.weeklyTotals)
          data = Object.values(this.weeklyTotals)
        } else if (this.timePeriod === 'monthly') {
          labels = Object.keys(this.monthlyTotals)
          data = Object.values(this.monthlyTotals)
        } else if (this.timePeriod === 'quarterly') {
          labels = Object.keys(this.quarterlyTotals)
          data = Object.values(this.quarterlyTotals)
        } else if (this.timePeriod === 'yearly') {
          labels = Object.keys(this.yearlyTotals)
          data = Object.values(this.yearlyTotals)
        }
        
        return {
          labels,
          datasets: [{
            label: 'Expenses',
            data,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
          }]
        }
      } else if (this.selectedView === 'comparison') {
        return this.getComparisonData()
      }
      return { labels: [], datasets: [] }
    },
    
    getSecondaryChartData() {
      if (this.selectedView === 'category') {
        return {
          labels: ['Income', 'Expenses'],
          datasets: [{
            data: [this.getTotalIncome(), this.getTotalExpenses()],
            backgroundColor: ['#27AE60', '#E74C3C'],
            borderWidth: 2,
            borderColor: '#fff'
          }]
        }
      } else if (this.selectedView === 'time') {
        return {
          labels: Object.keys(this.monthlyTotals),
          datasets: [{
            label: 'Monthly Expenses',
            data: Object.values(this.monthlyTotals),
            backgroundColor: '#667eea',
            borderColor: '#667eea',
            borderWidth: 1,
            borderRadius: 4
          }]
        }
      }
      return { labels: [], datasets: [] }
    },
    
    getComparisonData() {
      // This would implement period-to-period comparison
      // For now, returning basic comparison data
      const currentMonth = new Date().getMonth()
      const lastMonth = currentMonth - 1 >= 0 ? currentMonth - 1 : 11
      
      const currentMonthData = this.filteredTransactions
        .filter(t => new Date(t.date).getMonth() === currentMonth)
        .reduce((sum, t) => sum + parseFloat(t.amount), 0)
      
      const lastMonthData = this.filteredTransactions
        .filter(t => new Date(t.date).getMonth() === lastMonth)
        .reduce((sum, t) => sum + parseFloat(t.amount), 0)
      
      return {
        labels: ['Last Period', 'Current Period'],
        datasets: [{
          label: 'Total Amount',
          data: [lastMonthData, currentMonthData],
          backgroundColor: ['#36A2EB', '#667eea'],
          borderColor: ['#36A2EB', '#667eea'],
          borderWidth: 1
        }]
      }
    },
    
    // Chart options methods
    getMainChartOptions() {
      const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: '#2c3e50',
              padding: 20,
              font: { size: 12 }
            }
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                let value = context.parsed ?? context.raw;
                if (typeof value === 'object' && value !== null && 'y' in value) {
                  value = value.y;
                }
                value = Number(value);
                return `${context.label}: RM${isNaN(value) ? '0.00' : value.toFixed(2)}`
              }
            }
          }
        }
      }
      
      if (this.selectedView === 'time' || this.selectedView === 'comparison') {
        baseOptions.scales = {
          y: {
            beginAtZero: true,
            ticks: {
              color: '#2c3e50',
              callback: function(value) {
                return 'RM' + value.toFixed(0)
              }
            },
            grid: { color: 'rgba(44, 62, 80, 0.1)' }
          },
          x: {
            ticks: { color: '#2c3e50' },
            grid: { display: false }
          }
        }
      }
      
      return baseOptions
    },
    
    getSecondaryChartOptions() {
      return this.getMainChartOptions()
    },
    
    // Utility methods
    formatCurrency(amount) {
      const numAmount = parseFloat(amount)
      return 'RM' + (isNaN(numAmount) ? '0.00' : numAmount.toFixed(2))
    },
    
    getTotalIncome() {
      return this.filteredTransactions
        .filter(t => t.type === 'income')
        .reduce((sum, t) => sum + parseFloat(t.amount), 0)
    },
    
    getTotalExpenses() {
      return this.filteredTransactions
        .filter(t => t.type === 'expense')
        .reduce((sum, t) => sum + parseFloat(t.amount), 0)
    },
    
    getNetBalance() {
      return this.getTotalIncome() - this.getTotalExpenses()
    },
    
    getAverage() {
      return this.filteredTransactions.length ? 
        this.filteredTransactions.reduce((sum, t) => sum + parseFloat(t.amount), 0) / this.filteredTransactions.length : 0
    },
    
    getHighestExpense() {
      const expenses = this.filteredTransactions.filter(t => t.type === 'expense')
      return expenses.length ? 
        Math.max(...expenses.map(t => parseFloat(t.amount))) : 0
    },
    
    logout() {
      localStorage.removeItem('jwt_token')
      localStorage.removeItem('user_info')
      this.$router.push('/')
    }
  },
  mounted() {
    this.fetchTransactions()
  }
}
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

.statistics-view {
  margin-left: 240px;
  padding: 2rem 0;
  width: calc(100% - 240px);
}

h1 {
  color: #fff;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 2rem;
  text-align: center;
  text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.filter-controls {
  background: rgba(255,255,255,0.95);
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(10px);
}

.filter-group {
  margin-bottom: 1.5rem;
}

.filter-group:last-child {
  margin-bottom: 0;
}

.filter-label {
  display: block;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.filter-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.filter-btn {
  background-color: #f8f9fa;
  color: #2c3e50;
  border: 2px solid #e9ecef;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.filter-btn.active {
  background-color: #667eea;
  color: #fff;
  border-color: #667eea;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
}

.filter-btn:hover {
  border-color: #667eea;
  transform: translateY(-1px);
}

.date-range-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.date-input {
  padding: 0.5rem;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 0.875rem;
  color: #2c3e50;
  transition: border-color 0.3s ease;
}

.date-input:focus {
  outline: none;
  border-color: #667eea;
}

.date-separator {
  color: #6c757d;
  font-weight: 500;
}

.reset-btn {
  background: #e74c3c;
  color: #fff;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.reset-btn:hover {
  background: #c0392b;
  transform: translateY(-1px);
}

.loading-state,
.error-state {
  text-align: center;
  padding: 3rem;
  color: #fff;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255,255,255,0.3);
  border-top: 4px solid #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.retry-btn {
  background: #fff;
  color: #667eea;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  margin-top: 1rem;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 6px rgba(0,0,0,0.15);
}

.charts-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.card {
  background: rgba(255,255,255,0.95);
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  transition: transform 0.3s ease;
  backdrop-filter: blur(10px);
}

.card:hover {
  transform: translateY(-5px);
}

.chart-card {
  padding: 1.5rem;
}

.main-chart {
  grid-column: 1 / -1;
}

.chart-card h3 {
  margin-bottom: 1rem;
  text-align: center;
  color: #2c3e50;
  font-size: 1.25rem;
  font-weight: 600;
}

.chart-wrapper {
  height: 300px;
  position: relative;
}

.chart-container {
  height: 100%;
  width: 100%;
}

.no-data {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #6c757d;
}

.no-data-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.stats-summary {
  padding: 2rem;
}

.stats-summary h3 {
  margin-bottom: 2rem;
  color: #2c3e50;
  font-size: 1.5rem;
  font-weight: 600;
  text-align: center;
}

.summary-items {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.summary-item {
  text-align: center;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.summary-item h4 {
  margin-bottom: 0.5rem;
  color: #6c757d;
  font-size: 0.875rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.summary-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
}

.summary-value.income {
  color: #27ae60;
}

.summary-value.expense {
  color: #e74c3c;
}

.summary-value.positive {
  color: #27ae60;
}

.summary-value.negative {
  color: #e74c3c;
}

@media (max-width: 1200px) {
  .charts-container {
    grid-template-columns: 1fr;
  }
  
  .main-chart {
    grid-column: 1;
  }
}

@media (max-width: 900px) {
  .sidebar {
    width: 100vw;
    min-height: unset;
    flex-direction: row;
    align-items: center;
    justify-content: flex-start;
    padding: 1rem;
    position: static;
  }
  
  .statistics-view {
    margin-left: 0;
    width: 100%;
    padding: 1rem 0;
  }
  
  .dashboard {
    padding: 1rem;
  }
  
  .filter-buttons {
    justify-content: center;
  }
  
  .date-range-controls {
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .summary-items {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .filter-group {
    margin-bottom: 1rem;
  }
  
  .filter-controls {
    padding: 1rem;
  }
  
  h1 {
    font-size: 2rem;
  }
  
  .filter-btn {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
  }
  
  .date-input {
    padding: 0.4rem;
    font-size: 0.8rem;
  }
}

@media (max-width: 480px) {
  .summary-items {
    grid-template-columns: 1fr;
  }
  
  .filter-buttons {
    flex-direction: column;
    align-items: center;
  }
  
  .filter-btn {
    width: 100%;
    max-width: 200px;
  }
  
  .date-range-controls {
    flex-direction: column;
    align-items: center;
  }
  
  .date-input {
    width: 100%;
    max-width: 200px;
  }
  
  .chart-wrapper {
    height: 250px;
  }
  
  .stats-summary,
  .chart-card {
    padding: 1rem;
  }
  
  .filter-controls {
    padding: 0.75rem;
  }
}
</style>