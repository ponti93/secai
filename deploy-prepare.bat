@echo off
echo Preparing Laravel project for cPanel deployment...

REM Create deployment directory
if not exist "deploy" mkdir deploy

REM Copy project files (excluding development files)
xcopy /E /I /Y /EXCLUDE:exclude.txt . deploy\

echo.
echo Project prepared for deployment in 'deploy' folder
echo.
echo Next steps:
echo 1. Upload the 'deploy' folder contents to your cPanel public_html
echo 2. Set up your database in cPanel
echo 3. Update .env file with production settings
echo 4. Run migrations and seeders
echo.
pause
