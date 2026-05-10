# 🔔 Smart Notification System - Complete Setup Guide

## 📋 Project Overview

A fully functional real-time notification system with:
- User Authentication (Login & Sign Up)
- Dashboard with Badge-Style Notifications
- Admin Panel for Sending Alerts
- MQTT Real-time Messaging
- MySQL Database
- Topic-based Filtering
- Enterprise UI

---

## 🛠️ PREREQUISITES

1. **XAMPP** - Apache + MySQL + PHP
2. **Mosquitto MQTT Broker** - Message broker with WebSocket support
3. **Node.js** (optional - for package management)

---

## 📦 STEP 1: Install XAMPP

1. Download from: https://www.apachefriends.org/
2. Run installer and select:
   - Apache
   - MySQL
   - PHP
   - PhpMyAdmin
3. Choose installation directory (e.g., C:\xampp)
4. Complete installation

---

## 📦 STEP 2: Install Mosquitto MQTT Broker

1. Download from: https://mosquitto.org/download/
2. Install with default settings
3. Installation path: `C:\Program Files\mosquitto`

---

## 🔧 STEP 3: Configure Mosquitto for WebSocket

1. Open `C:\Program Files\mosquitto\mosquitto.conf`
2. **DELETE all existing content**
3. **Paste this:**

```conf
listener 1883
protocol mqtt

listener 9001
protocol websockets

allow_anonymous true
```

4. Save the file

---

## ▶️ STEP 4: Start Services

### Start Apache & MySQL:
1. Open XAMPP Control Panel
2. Click **Start** next to:
   - Apache
   - MySQL

### Start Mosquitto Broker:
1. Open Command Prompt
2. Run:
```bash
cd "C:\Program Files\mosquitto"
.\mosquitto.exe -v
```

You should see:
```
Opening websockets listen socket on port 9001.
```

**DO NOT CLOSE THIS WINDOW**

---

## 📁 STEP 5: Copy Project Files

1. Copy the entire `notif` folder to:
   ```
   C:\xampp\htdocs\mqtt-alert-app
   ```

2. Your structure should look like:
   ```
   C:\xampp\htdocs\mqtt-alert-app
   ├── index.html
   ├── login.html
   ├── signup.html
   ├── dashboard.html
   ├── admin.html
   ├── style.css
   ├── app.js
   ├── admin.js
   ├── config/
   │   └── db.php
   ├── backend/
   │   ├── register.php
   │   ├── login.php
   │   ├── logout.php
   │   └── check_session.php
   └── assets/
   ```

---

## 🗄️ STEP 6: Verify Database

1. Open: `http://localhost/phpmyadmin`
2. Login with:
   - Username: `root`
   - Password: (leave empty)

3. Check that `mqtt_alert_db` database exists with tables:
   - `users`
   - `notifications`
   - `sessions`

If not auto-created, the database will be created on first login.

---

## 🌐 STEP 7: Access the System

### User Dashboard:
```
http://localhost/mqtt-alert-app/
```
or
```
http://localhost/mqtt-alert-app/login.html
```

### Admin Panel:
```
http://localhost/mqtt-alert-app/admin.html
```

---

## 👤 STEP 8: Create Test Account

1. Go to Sign Up page
2. Create account with:
   - Username: `testuser`
   - Email: `test@example.com`
   - Password: `password123`

3. Login with credentials

---

## 🔔 FEATURES

### User Dashboard
- ✅ Real-time notifications (badge style)
- ✅ Filter by topic
- ✅ Mark as read
- ✅ Delete notifications
- ✅ Auto-refresh on new messages
- ✅ Toast notifications

### Admin Panel
- ✅ Send alerts to multiple topics
- ✅ Real-time MQTT publishing
- ✅ Topic selection
- ✅ Message validation

### Authentication
- ✅ User registration
- ✅ Secure login
- ✅ Session management
- ✅ Password hashing
- ✅ Logout functionality

---

## 📋 Database Schema

### Users Table
```sql
- id (PRIMARY KEY)
- username (UNIQUE)
- email (UNIQUE)
- password (hashed)
- created_at
```

### Notifications Table
```sql
- id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- topic
- message
- is_read
- created_at
```

### Sessions Table
```sql
- id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- session_token (UNIQUE)
- expires_at
- created_at
```

---

## 🔧 MQTT Topics

Default topics configured:
- `school/announcements` - 🎓 School announcements
- `system/alerts` - ⚙️ System alerts
- `weather/alerts` - 🌤️ Weather alerts

### Add New Topic

**In admin.html:**
Find `<select id="topic">` and add:
```html
<option value="your/topic">Your Topic</option>
```

**In app.js:**
Find `client.subscribe` section and add:
```javascript
client.subscribe("your/topic");
```

**In dashboard.html:**
Add filter button:
```html
<button onclick="filterTopic('your/topic')" class="filter-btn">Your Topic</button>
```

---

## 🎨 Customize Design

Edit `style.css` to change:
- Colors: Search for hex codes like `#0f172a`, `#2563eb`
- Fonts: Change `font-family`
- Sizes: Modify `padding`, `font-size`
- Animations: Update `@keyframes` sections

Example - Change background:
```css
/* Find this: */
body {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}

/* Change to: */
body {
    background: #000000;
}
```

---

## 🔊 Features You Can Add

1. **Sound Notifications** - Already implemented
2. **Email Alerts** - Integrate with SMTP
3. **Push Notifications** - Use Service Workers
4. **Dark/Light Mode** - Add theme toggle
5. **Message Priority** - Color code by importance
6. **File Attachments** - Upload images/docs
7. **User Roles** - Admin, Moderator, User
8. **Archive Messages** - Move old notifications
9. **Search Function** - Find past alerts
10. **Mobile App** - React Native version

---

## 🐛 Troubleshooting

### Can't connect to MQTT
- ✓ Ensure Mosquitto is running (check cmd window)
- ✓ Check firewall allows ports 1883 & 9001
- ✓ Verify mosquitto.conf has websockets on port 9001

### Database connection error
- ✓ Ensure MySQL is running in XAMPP
- ✓ Check db.php has correct credentials
- ✓ Verify database exists in PhpMyAdmin

### Login page blank/not loading
- ✓ Check Apache is running
- ✓ Verify files are in `C:\xampp\htdocs\mqtt-alert-app\`
- ✓ Try clearing browser cache

### Notifications not appearing
- ✓ Check Mosquitto broker is running
- ✓ Verify WebSocket connection on port 9001
- ✓ Check browser console for errors (F12)

---

## 📊 Testing the System

### Test Workflow

1. **Open 2 Browser Windows:**
   - Window 1: Dashboard (User)
   - Window 2: Admin Panel

2. **Send Test Alert:**
   - Go to Admin Panel
   - Select "System Alert"
   - Type: "Test notification"
   - Click "Send Alert"

3. **Check Dashboard:**
   - Badge appears immediately
   - Toast notification shows
   - Badge can be marked as read/deleted

4. **Test Filtering:**
   - Send messages to different topics
   - Use filter buttons to view by topic

---

## 🚀 Performance Tips

- Keep Mosquitto running in background
- Use browser developer tools to monitor
- Clear old notifications periodically
- Archive old messages (future feature)

---

## 📞 Support

For issues:
1. Check console (F12 in browser)
2. Check Mosquitto console
3. Verify all services running
4. Review troubleshooting section

---

## 📄 File Descriptions

| File | Purpose |
|------|---------|
| `login.html` | User login page |
| `signup.html` | User registration |
| `dashboard.html` | Main notification display |
| `admin.html` | Send alerts |
| `style.css` | All styling |
| `app.js` | Dashboard logic & MQTT |
| `admin.js` | Admin panel logic |
| `config/db.php` | Database setup |
| `backend/register.php` | Handle registration |
| `backend/login.php` | Handle login |
| `backend/logout.php` | Handle logout |
| `backend/check_session.php` | Verify session |

---

**System Ready! 🎉**

Start with: `http://localhost/mqtt-alert-app/`
