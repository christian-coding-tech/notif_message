# 🔔 Smart Notification System - Setup Guide

## 📋 Prerequisites

- **XAMPP** (Apache + MySQL + PHP)
- **Mosquitto MQTT Broker**
- **Modern Web Browser** (Chrome, Firefox, Edge, Safari)

---

## 🚀 STEP 1: Install XAMPP

1. Download from: https://www.apachefriends.org/
2. Install XAMPP
3. Open XAMPP Control Panel
4. Start **Apache** and **MySQL** services

### Verify MySQL
- Open browser: `http://localhost/phpmyadmin`
- Should see the phpMyAdmin interface

---

## 🚀 STEP 2: Install Mosquitto MQTT Broker

1. Download from: https://mosquitto.org/download/
2. Install Mosquitto
3. Navigate to: `C:\Program Files\mosquitto`

---

## 🚀 STEP 3: Configure Mosquitto

1. Open: `C:\Program Files\mosquitto\mosquitto.conf`
2. **DELETE ALL CONTENT**
3. Replace with:

```conf
listener 1883
protocol mqtt

listener 9001
protocol websockets

allow_anonymous true
```

4. Save the file

---

## 🚀 STEP 4: Copy Project Files

### Option A: Copy to XAMPP
Copy the entire `notif` folder to:
```
C:\xampp\htdocs\
```

### Option B: Copy to Desktop (for local testing)
Keep files in:
```
c:\Users\NIA\Desktop\notif
```

Then setup a local server (Python, Node.js, or VS Code Live Server)

---

## 🚀 STEP 5: Start Services

### Terminal 1: Start Mosquitto Broker
```bash
cd "C:\Program Files\mosquitto"
mosquitto -v
```

You should see:
```
Opening websockets listen socket on port 9001.
```

**✅ KEEP THIS TERMINAL OPEN**

### Terminal 2: Start Apache & MySQL
Open XAMPP Control Panel → Click "Start" for Apache and MySQL

---

## 🚀 STEP 6: Initialize Database

The database will be automatically created when you first access the application.

**Alternatively**, if using localhost directly:
1. Open: `http://localhost/phpmyadmin`
2. Click "New"
3. Create database: `mqtt_alert_db`
4. The app will create tables automatically on first run

---

## 🚀 STEP 7: Access the Application

### User Dashboard
```
http://localhost/notif
```

### Sign Up (First Time)
```
http://localhost/notif/signup.html
```

### Admin Panel (Send Alerts)
```
http://localhost/notif/admin.html
```

---

## 📁 Project Structure

```
notif/
├── index.html                      (Redirect to login)
├── login.html                      (User login page)
├── signup.html                     (User registration)
├── dashboard.html                  (Main notifications page with badges)
├── admin.html                      (Admin alert sender)
├── style.css                       (All styling - badge notifications)
├── app.js                          (Dashboard logic + MQTT)
├── admin.js                        (Admin panel logic + MQTT)
├── mqtt_alert_db_schema.sql        (Complete database schema & queries)
│
├── config/
│   └── db.php                      (Database configuration & setup)
│
├── backend/
│   ├── register.php                (User registration endpoint)
│   ├── login.php                   (User login endpoint)
│   ├── logout.php                  (Logout endpoint)
│   └── check_session.php           (Session verification)
│
└── assets/
    └── (Reserved for images, icons, etc.)
```

---

## ✨ Features Implemented

✅ **User Authentication**
- Sign up with username, email, password
- Login system with password hashing
- Session management
- Logout functionality

✅ **Real-time Notifications**
- MQTT WebSocket connection
- Real-time alert delivery
- Multiple topics support

✅ **Badge-Style Notifications**
- Modern card-based design
- Animated badge appearance
- Read/Unread status
- Color-coded topics

✅ **Dashboard Features**
- View all notifications
- Filter by topic
- Mark as read/unread
- Delete notifications
- Toast popup alerts
- Sound notifications

✅ **Admin Panel**
- Send alerts to specific topics
- Real-time message publishing
- Topic selection dropdown

✅ **Enterprise UI**
- Dark theme with gradient
- Responsive design
- Smooth animations
- Professional styling

---

## 🎯 How to Use

### 1. Create Account
1. Go to: `http://localhost/notif/signup.html`
2. Enter username, email, password
3. Click "Sign Up"

### 2. Login
1. Go to: `http://localhost/notif/login.html`
2. Enter credentials
3. Click "Login"
4. You'll be redirected to Dashboard

### 3. View Notifications (Dashboard)
1. You'll see badge-style notifications
2. Filter by topic using buttons
3. Mark notifications as read
4. Delete notifications
5. Listen for toast popups with sound

### 4. Send Alerts (Admin Panel)
1. Go to: `http://localhost/notif/admin.html`
2. Select topic (School, System, or Weather)
3. Type message
4. Click "Send Alert"
5. All logged-in users receive notification in real-time

---

## 🔥 Add New Topics

### Step 1: Edit admin.html
Find the `<select id="topic">` section, add:
```html
<option value="emergency/alerts">
    Emergency Alerts
</option>
```

### Step 2: Edit app.js
Find `client.subscribe("weather/alerts");` section, add:
```javascript
client.subscribe("emergency/alerts");
```

### Step 3: Edit dashboard.html
Find `<div class="filter-buttons">` section, add:
```html
<button onclick="filterTopic('emergency/alerts')" class="filter-btn">Emergency</button>
```

### Step 4: Update getTopicEmoji (app.js)
Add emoji for new topic:
```javascript
'emergency/alerts': '🚨'
```

---

## 🎨 Customize Design

### Change Color Scheme
Edit `style.css` and modify:

**Primary Blue** → Change all `#2563eb` to your color

**Dark Background** → Change `#0f172a` to your color

**Accent** → Change `#60a5fa` to your color

### Example: Change to Purple Theme
Replace in `style.css`:
```
#2563eb → #8b5cf6 (purple-600)
#60a5fa → #a78bfa (purple-400)
#1d4ed8 → #7c3aed (purple-700)
```

---

## 🔊 Customize Notification Sound

Edit `app.js`, find `playNotificationSound()` function:

Change the audio URL to your own sound:
```javascript
const audio = new Audio('YOUR_AUDIO_URL_HERE');
```

Free sound sources:
- Freesound.org
- Notificationsounds.com
- Zapsplat.com

---

## 🐛 Troubleshooting

### Problem: "Connection refused" on MQTT
**Solution:** Make sure Mosquitto is running. Check Terminal 1 shows "Opening websockets listen socket on port 9001"

### Problem: Database error
**Solution:** 
1. Ensure MySQL is running in XAMPP
2. Check phpmyadmin: `http://localhost/phpmyadmin`
3. If needed, create database manually: `mqtt_alert_db`

### Problem: Pages show "Connection error"
**Solution:**
1. Check if Apache is running
2. Verify files are in `C:\xampp\htdocs\notif\`

### Problem: Admin panel not accessible
**Solution:**
1. Ensure user account has admin role set in database
2. Use phpmyadmin to update: `UPDATE users SET role='admin' WHERE username='your_username'`

---

## 👨‍💼 ADMIN PANEL GUIDE

### Creating an Admin User

#### Method 1: Using phpMyAdmin (Recommended)

1. **Register a normal user account**
   - Go to: `http://localhost/notif/signup.html`
   - Create an account (e.g., username: `admin`, email: `admin@localhost`)

2. **Convert to Admin via phpMyAdmin**
   - Open: `http://localhost/phpmyadmin`
   - Navigate to: `mqtt_alert_db` → `users` table
   - Find your user row
   - Click "Edit"
   - Change `role` field from `user` to `admin`
   - Click "Go"

3. **Login to Admin Panel**
   - Go to: `http://localhost/notif/login.html`
   - Login with your admin credentials
   - You'll be redirected to: `http://localhost/notif/admin.html`

#### Method 2: Direct SQL (If you know SQL)

```sql
-- Login first with your user account
UPDATE users SET role='admin' WHERE username='your_username';
```

### Admin Panel Features

The enhanced admin panel provides:

#### 📊 Dashboard
- **Active Alerts Count** - Number of currently active alerts
- **Total Alerts Count** - Total alerts ever created
- **Archived Alerts Count** - Archived/inactive alerts
- **Recent Alerts** - Quick view of last 5 alerts

#### ✍️ Create New Alert
- **Select Topic** - Choose from multiple alert channels
- **Alert Type** - Choose announcement, warning, or emergency
- **Message** - Write detailed alert message (max 500 chars)
- **Character Counter** - Real-time character count
- **Send Alert** - Publish to all subscribed users

#### 🔧 Manage Alerts
- **View All Alerts** - See all created alerts with details
- **Filter by Status** - View active or archived alerts
- **View Alert Details** - See full message and metadata
- **Archive/Unarchive** - Change alert status
- **Delete Alerts** - Remove alerts permanently
- **Timestamps** - See when alerts were created

#### Sidebar Navigation
- **Clean Navigation** - Easy access to all sections
- **Responsive Sidebar** - Works on mobile/tablet
- **User Profile** - Shows current admin username
- **Quick Logout** - Convenient logout button

### Alert Topics

The system supports these default topics:

```
📚 School Announcements (school/announcements)
   - Important school events
   - Schedule changes
   - Administrative notices

⚙️ System Alerts (system/alerts)
   - System maintenance
   - Technical updates
   - Service announcements

🌦️ Weather Alerts (weather/alerts)
   - Weather warnings
   - Severe weather updates
   - Climate information
```

### Adding Custom Topics

To add a new alert topic (e.g., "Campus Safety"):

#### Step 1: Update Admin Panel
Edit `admin.html`, find the `<select id="topic">` element:

```html
<option value="safety/alerts">🛡️ Campus Safety</option>
```

#### Step 2: Update Database (Optional)
The topics are stored in alerts table, no schema change needed.

#### Step 3: Update User Dashboard
Edit `app.js`, find `getTopicLabel()` function, add:

```javascript
'safety/alerts': '🛡️ Safety'
```

### Admin Panel Permissions

**Only admins can:**
- ✅ Access the admin panel
- ✅ Create new alerts
- ✅ Delete alerts
- ✅ Archive/unarchive alerts
- ✅ View all alerts on the system

**Regular users cannot:**
- ❌ Access admin.html (redirected to dashboard)
- ❌ Create or delete alerts
- ❌ See admin management features

### Managing Multiple Admins

To create multiple admin accounts:

1. Have each admin user register normally via signup
2. Use phpMyAdmin to update their role to 'admin'
3. Each admin can then:
   - Create alerts independently
   - See all alerts created by all admins
   - Delete only their own alerts (or modify to allow deleting any)

### Alert Best Practices

✅ **DO:**
- Use clear, concise messaging
- Choose appropriate alert types
- Include important details
- Archive old alerts for organization
- Test alerts before sending to all users

❌ **DON'T:**
- Send test alerts to all users repeatedly
- Use extremely long messages
- Create duplicate alerts
- Leave test alerts active

---
3. Try clearing browser cache (Ctrl + Shift + Delete)

### Problem: No notifications received
**Solution:**
1. Check Mosquitto is running
2. Check browser console for errors (F12)
3. Make sure you're logged in
4. Try sending test message from Admin panel

### Problem: Notifications not showing sound
**Solution:**
1. Check browser volume settings
2. Some browsers mute by default - click audio button
3. Check browser permissions for audio
4. Try different browser if issue persists

---

## 📊 Database Schema

### users table
```sql
id (INT) - Primary Key
username (VARCHAR 50) - Unique
email (VARCHAR 100) - Unique
password (VARCHAR 255) - Hashed
created_at (TIMESTAMP) - Default: now
```

### notifications table
```sql
id (INT) - Primary Key
user_id (INT) - Foreign Key (users.id)
topic (VARCHAR 100) - Alert category
message (TEXT) - Alert content
is_read (BOOLEAN) - Read status
created_at (TIMESTAMP) - Default: now
```

### sessions table
```sql
id (INT) - Primary Key
user_id (INT) - Foreign Key (users.id)
session_token (VARCHAR 255) - Unique
expires_at (TIMESTAMP) - Expiration time
created_at (TIMESTAMP) - Default: now
```

---

## 🚀 Advanced Features to Add

### Phase 2 - Enhancement Ideas
1. **Email Notifications** - Send alerts via email
2. **SMS Alerts** - Integrate Twilio
3. **User Preferences** - Choose notification types
4. **Notification History** - Store in database
5. **Search Notifications** - Find by keyword
6. **Dark/Light Mode Toggle** - Theme switcher
7. **Export Data** - Download notifications as CSV
8. **Role-Based Access** - Admin vs User roles
9. **Scheduled Alerts** - Send at specific times
10. **Analytics Dashboard** - View usage stats

---

## 📞 Support

If you encounter issues:

1. **Check Console Errors** - Press F12 in browser
2. **Check Network** - Network tab shows API calls
3. **Check Services** - Ensure Apache, MySQL, Mosquitto are running
4. **Check Ports** - 80 (Apache), 3306 (MySQL), 1883/9001 (Mosquitto)

---

## ✅ Verification Checklist

- [ ] XAMPP installed and running
- [ ] Mosquitto installed and running
- [ ] Mosquitto.conf configured with websockets
- [ ] Files copied to `C:\xampp\htdocs\notif\`
- [ ] Can access `http://localhost/notif/signup.html`
- [ ] Can create account
- [ ] Can login
- [ ] Dashboard loads with badge notifications
- [ ] Admin panel loads
- [ ] Can send test alert
- [ ] Notifications appear in real-time
- [ ] Toast popup shows with sound
- [ ] Logout works

---

## 🎉 You're All Set!

Your Smart Notification System is now fully functional with:
✅ User authentication (Sign up/Login)
✅ Database storage (MySQL)
✅ Real-time notifications (MQTT)
✅ Badge-style UI
✅ Admin alert system
✅ Professional design

**Happy notifying! 🔔**
