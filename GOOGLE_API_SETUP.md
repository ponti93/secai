# Google API Integration Setup Guide

This guide will help you set up Google API integration for Gmail and Calendar functionality in the AI Secretary System.

## Prerequisites

1. A Google Cloud Platform account
2. Access to Google Cloud Console
3. The AI Secretary System backend and frontend running

## Step 1: Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click "Select a project" → "New Project"
3. Enter project name: "AI Secretary System"
4. Click "Create"

## Step 2: Enable Required APIs

1. In the Google Cloud Console, go to "APIs & Services" → "Library"
2. Search for and enable the following APIs:
   - **Gmail API**
   - **Google Calendar API**

## Step 3: Create OAuth 2.0 Credentials

1. Go to "APIs & Services" → "Credentials"
2. Click "Create Credentials" → "OAuth 2.0 Client IDs"
3. If prompted, configure the OAuth consent screen:
   - Choose "External" user type
   - Fill in required fields:
     - App name: "AI Secretary System"
     - User support email: your email
     - Developer contact: your email
   - Add scopes:
     - `https://www.googleapis.com/auth/gmail.readonly`
     - `https://www.googleapis.com/auth/gmail.send`
     - `https://www.googleapis.com/auth/gmail.modify`
     - `https://www.googleapis.com/auth/calendar`
     - `https://www.googleapis.com/auth/calendar.events`
   - Add test users (your email address)

4. Create OAuth 2.0 Client ID:
   - Application type: "Web application"
   - Name: "AI Secretary System Web Client"
   - Authorized redirect URIs:
     - `http://localhost:3000/auth/google/callback` (for development)
     - `https://secretaryaiproject.vercel.app/api/google/callback` (for production)

5. Download the credentials JSON file

## Step 4: Configure Environment Variables

Add the following environment variables to your backend `.env` file:

```env
# Google OAuth Configuration
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost:3000/auth/google/callback

# For production, use your actual domain:
# GOOGLE_REDIRECT_URI=https://secretaryaiproject.vercel.app/api/google/callback
```

## Step 5: Database Setup

Run the following SQL in your Supabase database to create the Google tokens table:

```sql
-- Create table for storing Google OAuth tokens
CREATE TABLE IF NOT EXISTS user_google_tokens (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  access_token TEXT NOT NULL,
  refresh_token TEXT,
  token_type VARCHAR(50) DEFAULT 'Bearer',
  expiry_date BIGINT,
  scope TEXT,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  UNIQUE(user_id)
);

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_user_google_tokens_user_id ON user_google_tokens(user_id);

-- Enable Row Level Security
ALTER TABLE user_google_tokens ENABLE ROW LEVEL SECURITY;

-- Create RLS policy
CREATE POLICY "Users can only access their own Google tokens" ON user_google_tokens
  FOR ALL USING (auth.uid() = user_id);
```

## Step 6: Install Dependencies

The required dependencies are already included in the backend `package.json`:

- `googleapis`: ^118.0.0

## Step 7: Test the Integration

1. Start your backend server
2. Start your frontend application
3. Navigate to the Emails page
4. Click "Connect Google Account"
5. Complete the OAuth flow
6. Verify that Gmail messages are loaded

## Features Available After Setup

### Gmail Integration
- **Read Emails**: View Gmail messages in the AI Secretary interface
- **Send Emails**: Compose and send emails through Gmail
- **Reply to Emails**: Reply to Gmail messages
- **AI Email Summarization**: Get AI-powered summaries of emails
- **AI Draft Replies**: Generate AI-powered email replies

### Google Calendar Integration
- **View Events**: See Google Calendar events
- **Create Events**: Add new events to Google Calendar
- **Update Events**: Modify existing calendar events
- **Delete Events**: Remove events from Google Calendar
- **AI Scheduling**: Get AI-powered optimal meeting time suggestions
- **Calendar Sync**: Sync Google Calendar with local calendar

### Meeting Recording
- **Record Meetings**: Use device microphone to record meetings
- **AI Transcription**: Automatically transcribe recorded meetings
- **AI Summarization**: Generate meeting summaries
- **Action Items Extraction**: Extract action items from meetings

## Troubleshooting

### Common Issues

1. **"Google account not connected" error**
   - Ensure the user has completed the OAuth flow
   - Check that tokens are stored in the database
   - Verify environment variables are set correctly

2. **"Invalid redirect URI" error**
   - Ensure the redirect URI in Google Cloud Console matches your environment variable
   - Check that the URI is exactly the same (including protocol and port)

3. **"Insufficient permissions" error**
   - Verify that all required scopes are added to the OAuth consent screen
   - Ensure the user has granted all necessary permissions

4. **"API not enabled" error**
   - Check that Gmail API and Google Calendar API are enabled in Google Cloud Console

### Debug Mode

To enable debug logging, add this to your backend `.env`:

```env
DEBUG=googleapis:*
```

## Security Considerations

1. **Token Storage**: Tokens are encrypted and stored securely in the database
2. **Row Level Security**: Supabase RLS ensures users can only access their own tokens
3. **Token Refresh**: The system automatically refreshes expired tokens
4. **Scope Limitation**: Only necessary scopes are requested

## Production Deployment

For production deployment:

1. Update the redirect URI to your production domain
2. Ensure your domain is verified in Google Cloud Console
3. Update the OAuth consent screen to "Production" status
4. Add your production domain to authorized domains
5. Update environment variables with production values

## Support

If you encounter issues:

1. Check the browser console for errors
2. Check the backend logs for API errors
3. Verify all environment variables are set correctly
4. Ensure the Google Cloud project is properly configured

## Next Steps

After completing the setup:

1. Test all Gmail and Calendar features
2. Set up meeting recording functionality
3. Configure AI features for email and calendar
4. Deploy to production with proper security measures
