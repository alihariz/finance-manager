<template>
  <div class="register-container">
    <h2>Create Your Account</h2>
    <form @submit.prevent="register" autocomplete="off">
      <div class="form-row">
        <label for="username">Username</label>
        <input v-model="form.username" id="username" required placeholder="Enter username" />
      </div>
      <div class="form-row">
        <label for="password">Password</label>
        <input v-model="form.password" id="password" type="password" required placeholder="Enter password" />
      </div>
      <button type="submit" class="register-btn">Register</button>
    </form>
    <div v-if="errorMessage" class="error-msg">{{ errorMessage }}</div>
    <div v-if="successMessage" class="success-msg">{{ successMessage }}</div>
    <div class="login-link">
      Already have an account?
      <router-link to="/">Login here</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MyRegister',
  data() {
    return {
      form: {
        username: '',
        password: '',
        email: '',
        role: 'staff',
        full_name: ''
      },
      errorMessage: '',
      successMessage: ''
    };
  },
  methods: {
    async register() {
      this.errorMessage = '';
      this.successMessage = '';
      try {
        const res = await fetch('http://localhost:8000/api/auth/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form)
        });
        const data = await res.json();
        if (res.ok && data.message) {
          this.successMessage = data.message + ' You can now log in.';
          this.form = { username: '', password: '', email: '', role: 'staff', full_name: '' };
        } else {
          this.errorMessage = data.error || 'Registration failed.';
        }
      } catch {
        this.errorMessage = 'Network error. Please try again.';
      }
    }
  }
};
</script>

<style scoped>
.register-container {
  max-width: 420px;
  margin: 60px auto;
  padding: 32px 28px;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

h2 {
  text-align: center;
  color: #2c3e50;
  font-weight: 700;
  margin-bottom: 24px;
  font-size: 1.8rem;
}

form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.form-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

label {
  font-weight: 600;
  color: #495057;
  font-size: 0.95rem;
}

input, select {
  padding: 0.75rem 1rem;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 1rem;
  background: white;
  transition: all 0.3s ease;
}

input:focus, select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.register-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.register-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.success-msg, .error-msg {
  margin-top: 1rem;
  padding: 1rem;
  border-radius: 8px;
  font-weight: 500;
  text-align: center;
}

.success-msg {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.error-msg {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.login-link {
  margin-top: 1.2rem;
  text-align: center;
  font-size: 0.95rem;
}

.login-link a {
  color: #667eea;
  font-weight: 600;
  text-decoration: none;
}

.login-link a:hover {
  text-decoration: underline;
}
</style>
