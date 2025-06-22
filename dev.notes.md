APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com

LOG_CHANNEL=stack
LOG_LEVEL=info

# Use this if you're deploying with MySQL

DB_CONNECTION=sqlite

# Session and cache (can be database or file)

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

# OPTIONAL - for storage:link

APP_STORAGE=/opt/render/project/storage

# Mail (optional)

MAIL_MAILER=log
MAIL_FROM_ADDRESS=no-reply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
