const axios = require('axios');

const BASE_URL = 'https://secretaryaiproject-posi7bdat-ponti93s-projects.vercel.app/api';

async function checkMongoDBConnection() {
  console.log('🔍 Checking MongoDB Connection Details...\n');

  try {
    // Test 1: Health Check
    console.log('1. Health Check...');
    try {
      const healthResponse = await axios.get(`${BASE_URL}/health`);
      console.log('✅ Health Check:', healthResponse.status, healthResponse.data);
    } catch (error) {
      console.log('❌ Health Check Failed:', error.response?.status, error.response?.data || error.message);
    }

    // Test 2: Database Test
    console.log('\n2. Database Test...');
    try {
      const dbResponse = await axios.get(`${BASE_URL}/test-db`);
      console.log('✅ Database Test:', dbResponse.status, dbResponse.data);
    } catch (error) {
      console.log('❌ Database Test Failed:', error.response?.status, error.response?.data || error.message);
    }

    // Test 3: Try to get more detailed error information
    console.log('\n3. Testing with detailed error logging...');
    try {
      const testEmail = `test-${Date.now()}@example.com`;
      const registerResponse = await axios.post(`${BASE_URL}/auth/register`, {
        name: 'Test User',
        email: testEmail,
        password: 'test123'
      });
      console.log('✅ Registration Success:', registerResponse.status, registerResponse.data);
    } catch (error) {
      console.log('❌ Registration Failed:', error.response?.status, error.response?.data || error.message);
      if (error.response?.data) {
        console.log('   Full Error Response:', JSON.stringify(error.response.data, null, 2));
      }
    }

  } catch (error) {
    console.log('❌ General Error:', error.message);
  }

  console.log('\n🏁 MongoDB Connection Check Complete!');
}

checkMongoDBConnection();


