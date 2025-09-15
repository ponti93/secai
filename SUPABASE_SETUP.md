# 🗄️ Supabase Database Setup Guide

This guide will help you set up the Supabase database for the AI Secretary System.

## 🔧 **Option 1: Automatic Setup (Recommended)**

### Step 1: Get Your Supabase Credentials
1. Go to your [Supabase Dashboard](https://supabase.com/dashboard)
2. Select your project
3. Go to **Settings** → **API**
4. Copy these values:
   - **Project URL** (SUPABASE_URL)
   - **Service Role Key** (SUPABASE_SERVICE_ROLE_KEY) - **Keep this secret!**

### Step 2: Set Environment Variables
Add these to your `backend/.env` file:
```env
SUPABASE_URL=https://your-project-id.supabase.co
SUPABASE_SERVICE_ROLE_KEY=your-service-role-key-here
SUPABASE_ANON_KEY=your-anon-key-here
```

### Step 3: Run the Setup Script
```bash
cd backend
npm run setup-db
```

This will automatically create all the required tables and configurations.

## 🔧 **Option 2: Manual Setup**

If the automatic setup doesn't work, you can set up the database manually:

### Step 1: Open Supabase SQL Editor
1. Go to your [Supabase Dashboard](https://supabase.com/dashboard)
2. Select your project
3. Go to **SQL Editor**

### Step 2: Run the SQL Script
Copy and paste the entire contents of `backend/database-setup.sql` into the SQL Editor and run it.

## 🧪 **Test the Setup**

After setup, test that everything works:

1. **Visit your deployed app**: https://secretaryaiproject-7kkgargah-ponti93s-projects.vercel.app
2. **Go to Calendar page**
3. **Try creating an event**
4. **Should work without "temporary storage" message**

## 📋 **What Gets Created**

The setup creates these database tables:

### `calendar_events`
- Stores all calendar events
- Includes: title, description, start/end times, location, attendees, etc.
- Has proper indexing for performance

### `user_google_tokens`
- Stores Google OAuth tokens for calendar integration
- Includes: access tokens, refresh tokens, expiry dates
- Enables Google Calendar sync

### Security Features
- **Row Level Security (RLS)** enabled
- **Policies** ensure users can only access their own data
- **Automatic timestamps** for created_at and updated_at

## 🚨 **Troubleshooting**

### Error: "relation does not exist"
- The tables weren't created properly
- Try running the setup script again
- Or use the manual SQL approach

### Error: "permission denied"
- Check that you're using the Service Role Key (not the anon key)
- Ensure the key has the correct permissions

### Error: "connection failed"
- Verify your SUPABASE_URL is correct
- Check that your Supabase project is active

## 🔐 **Security Notes**

- **Never commit** your Service Role Key to version control
- **Use environment variables** for all sensitive data
- **The Service Role Key** bypasses RLS - keep it secure
- **Regular users** will use the anon key which respects RLS

## 📞 **Need Help?**

If you're still having issues:
1. Check the Supabase logs in your dashboard
2. Verify your environment variables are set correctly
3. Try the manual SQL setup approach
4. Contact support with the specific error messages

---

**Once setup is complete, your calendar will have persistent storage and all features will work properly!** 🎉