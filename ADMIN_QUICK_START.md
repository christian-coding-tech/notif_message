# ⚡ Quick Start - Admin Panel Setup

## 🎯 What's New?

Your notification system now has a **professional admin panel** with:
- ✅ Role-based access control (admin vs regular users)
- ✅ Sidebar navigation
- ✅ Dashboard with statistics
- ✅ Complete alert management (create, delete, archive)
- ✅ Modern, responsive UI design
- ✅ Character counter for messages
- ✅ Filter and search alerts

---

## 🚀 5-Minute Setup

### Step 1: Update Your Database
The database schema has been updated with:
- `role` column in `users` table
- New `alerts` table for storing created alerts
- Updated `notifications` table

**The app will auto-create these** when you access it, BUT to be safe, you can manually run:

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select `mqtt_alert_db`
3. Click SQL tab
4. Copy the contents of `mqtt_alert_db_schema.sql`
5. Paste and execute

### Step 2: Create an Admin Account

#### Quick Method via phpMyAdmin:

1. **Register a user first:**
   - Go to: `http://localhost/notif/signup.html`
   - Create account (e.g., username: `admin`, password: `admin123`)

2. **Make user an admin:**
   - Open: `http://localhost/phpmyadmin`
   - Click: `mqtt_alert_db` → `users` table
   - Find your user row
   - Click "Edit"
   - Change `role` from `user` to `admin`
   - Click "Go"

### Step 3: Login to Admin Panel

1. Go to: `http://localhost/notif/login.html`
2. Enter your admin credentials
3. You'll be redirected to: `http://localhost/notif/admin.html`

---

## 📋 What Changed?

### New Files
- `backend/admin_api.php` - Backend API for admin operations

### Modified Files
- `admin.html` - Completely redesigned with sidebar
- `admin.js` - Full rewrite with new features
- `login.html` - Now stores user role and smart redirects
- `style.css` - Added sidebar, admin panels, and responsive styles
- `backend/login.php` - Now returns user role
- `backend/check_session.php` - Now returns user role
- `mqtt_alert_db_schema.sql` - Updated with new tables and columns

---

## 🎮 How to Use Admin Panel

### Dashboard
- See **Active Alerts**, **Total Alerts**, and **Archived Alerts** counters
- Quick view of **Recent 5 Alerts**

### Create Alert
1. Select a topic (School, System, or Weather)
2. Choose alert type (Announcement, Warning, Emergency)
3. Write your message (max 500 characters)
4. Click "Send Alert"
5. ✅ Alert publishes to all users instantly

### Manage Alerts
1. View all alerts you've created
2. Filter by: **All**, **Active**, or **Archived**
3. Actions for each alert:
   - 👁️ View - See full details
   - 📦 Archive - Archive for later
   - 🗑️ Delete - Remove permanently

---

## 🔐 Security & Permissions

### Who Can Access Admin Panel?
- ✅ Users with role = `admin`
- ❌ Regular users (will be denied access)

### What Can Admins Do?
- ✅ Create new alerts
- ✅ Delete their own alerts
- ✅ Archive/unarchive alerts
- ✅ View all system alerts

### What Can Regular Users Do?
- ✅ Receive alerts
- ✅ View alerts on their dashboard
- ✅ Mark alerts as read/unread
- ✅ Delete alerts from their view
- ❌ Cannot create alerts
- ❌ Cannot delete system alerts

---

## 🎨 Features Walkthrough

### Sidebar Navigation
The sidebar on the left provides quick access to:
- 📊 **Dashboard** - Overview and statistics
- ✍️ **Create Alert** - Send new alerts
- 🔧 **Manage Alerts** - View and manage all alerts

### Responsive Design
- **Desktop**: Full sidebar visible
- **Tablet**: Sidebar collapses to hamburger menu
- **Mobile**: Full responsive layout with touch-friendly buttons

### Dark Modern Theme
- Professional dark interface
- Blue accent colors
- Smooth animations
- Easy on the eyes

---

## 📱 Mobile Support

The admin panel works on:
- ✅ Desktop (Chrome, Firefox, Edge, Safari)
- ✅ Tablet (iPad, Android tablets)
- ✅ Mobile (iPhone, Android phones)

On mobile, the sidebar becomes a hamburger menu that slides in from the left.

---

## 🧪 Testing Your Setup

1. **Login as Admin:**
   - Navigate to: `http://localhost/notif/login.html`
   - Enter admin credentials
   - Should redirect to: `http://localhost/notif/admin.html`

2. **Create Test Alert:**
   - Go to "Create Alert" section
   - Select "School Announcement" topic
   - Type: "This is a test alert"
   - Click "Send Alert"
   - Should see: "✅ Alert sent successfully!"

3. **Verify in Dashboard:**
   - Go to "Dashboard" section
   - Should see "Total Alerts" count increase
   - Should see your alert in "Recent Alerts"

4. **Test Management:**
   - Go to "Manage Alerts"
   - Should see your created alert
   - Click archive/delete buttons to test

---

## ⚙️ Adding New Topics

If you want to add a new alert topic (e.g., "Campus Safety"):

### Step 1: Edit `admin.html`
Find this section (around line 45):
```html
<select id="topic" class="form-control">
    <option value="">-- Select Topic --</option>
    <option value="school/announcements">📚 School Announcement</option>
    <option value="system/alerts">⚙️ System Alert</option>
    <option value="weather/alerts">🌦️ Weather Alerts</option>
</select>
```

Add your topic:
```html
    <option value="safety/alerts">🛡️ Campus Safety</option>
```

### Step 2: Update `app.js` (for users to receive)
Find this section (around line 40 in app.js):
```javascript
client.subscribe("school/announcements");
client.subscribe("system/alerts");
client.subscribe("weather/alerts");
```

Add your topic:
```javascript
client.subscribe("safety/alerts");
```

### Step 3: Done! ✅
The topic is now available for creating and receiving alerts.

---

## 🐛 Troubleshooting

### "Access Denied! Only admins can access this panel"
- Your account is not an admin
- Use phpMyAdmin to set your role to 'admin'

### "Connection error" on page load
- Make sure Mosquitto MQTT broker is running
- Check if Apache and MySQL are running
- Refresh the page

### Admin panel is blank/white
- Check browser console (F12 → Console tab)
- Look for red error messages
- Refresh with Ctrl+Shift+R (hard refresh)

### Alert doesn't appear after creating
- Refresh the page
- Check if regular users are subscribed to that topic
- Verify MQTT broker is running

### Can't login with admin account
- Double-check username and password
- Make sure the role is set to 'admin' in phpMyAdmin
- Try logging out and in again

---

## 📚 Documentation

For more detailed information, see:
- `ADMIN_PANEL_GUIDE.md` - Complete admin panel documentation
- `SETUP_GUIDE.md` - Full system setup instructions
- `README.md` - General system overview

---

## 🎯 Next Steps

1. **Test the admin panel** with the steps above
2. **Create some test alerts** to verify functionality
3. **Customize** the design if desired (colors, fonts, etc.)
4. **Add more admins** by converting regular users in phpMyAdmin
5. **Deploy** when ready

---

**Congratulations!** 🎉 Your admin panel is now ready to use!

**Questions?** Check the documentation files or run through the troubleshooting guide.
