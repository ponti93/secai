const axios = require('axios');

const BASE_URL = 'https://secretaryaiproject-ffib47yni-ponti93s-projects.vercel.app';

async function addDefaultUser() {
  console.log('👤 Adding default admin user to database...\n');

  try {
    // Add the default admin user
    console.log('📝 Adding admin user to database...');
    const registerResponse = await axios.post(`${BASE_URL}/api/auth/register`, {
      name: 'Admin User',
      email: 'admin@admin.com',
      password: 'admin123',
      role: 'admin'
    });
    
    console.log('✅ Admin user added successfully:', registerResponse.data);
    
    // Test login with the new user
    console.log('\n🔑 Testing login with admin user...');
    const loginResponse = await axios.post(`${BASE_URL}/api/auth/login`, {
      email: 'admin@admin.com',
      password: 'admin123'
    });
    
    console.log('✅ Login successful:', loginResponse.data);
    
    // Test search functionality with the token
    const token = loginResponse.data.token;
    console.log('\n🔍 Testing search functionality...');
    const searchResponse = await axios.get(`${BASE_URL}/api/ai/search?q=project`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    console.log('✅ Search working:', searchResponse.data);
    
    console.log('\n🎉 Default user setup complete!');
    console.log('📋 Login credentials:');
    console.log('   Email: admin@admin.com');
    console.log('   Password: admin123');
    
  } catch (error) {
    if (error.response?.status === 400 && error.response?.data?.message?.includes('already exists')) {
      console.log('ℹ️ Admin user already exists, testing login...');
      
      // Test login with existing user
      const loginResponse = await axios.post(`${BASE_URL}/api/auth/login`, {
        email: 'admin@admin.com',
        password: 'admin123'
      });
      
      console.log('✅ Login successful:', loginResponse.data);
      
      // Test search functionality
      const token = loginResponse.data.token;
      console.log('\n🔍 Testing search functionality...');
      const searchResponse = await axios.get(`${BASE_URL}/api/ai/search?q=project`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      
      console.log('✅ Search working:', searchResponse.data);
      
      console.log('\n🎉 Default user is working!');
      console.log('📋 Login credentials:');
      console.log('   Email: admin@admin.com');
      console.log('   Password: admin123');
      
    } else {
      console.log('❌ Error:', error.response?.data || error.message);
      if (error.response?.status) {
        console.log('Status:', error.response.status);
      }
    }
  }
}

addDefaultUser();
