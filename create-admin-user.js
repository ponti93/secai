const axios = require('axios');

const BASE_URL = 'https://secretaryaiproject-posi7bdat-ponti93s-projects.vercel.app/api';

async function createAdminUser() {
  console.log('🔧 Creating Admin User...\n');

  try {
    // First, try to create the admin user
    console.log('1. Creating Admin User...');
    try {
      const registerResponse = await axios.post(`${BASE_URL}/auth/register`, {
        name: 'Admin User',
        email: 'admin@admin.com',
        password: 'admin123',
        role: 'admin'
      });
      console.log('✅ Admin User Created:', registerResponse.status, registerResponse.data);
    } catch (error) {
      console.log('❌ Admin User Creation Failed:', error.response?.status, error.response?.data || error.message);
      if (error.response?.data?.message) {
        console.log('   Error Details:', error.response.data.message);
      }
    }

    // Then try to login
    console.log('\n2. Testing Admin Login...');
    try {
      const loginResponse = await axios.post(`${BASE_URL}/auth/login`, {
        email: 'admin@admin.com',
        password: 'admin123'
      });
      console.log('✅ Admin Login Success:', loginResponse.status, loginResponse.data);
    } catch (error) {
      console.log('❌ Admin Login Failed:', error.response?.status, error.response?.data || error.message);
      if (error.response?.data?.message) {
        console.log('   Error Details:', error.response.data.message);
      }
    }

  } catch (error) {
    console.log('❌ General Error:', error.message);
  }

  console.log('\n🏁 Admin User Creation Complete!');
}

createAdminUser();
