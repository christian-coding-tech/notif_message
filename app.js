// Initialize MQTT Client
const client = mqtt.connect("ws://localhost:9001");

let allNotifications = [];
let currentUser = {};
let currentFilter = 'all';
let refreshInterval = null;

// Check if user is logged in
document.addEventListener('DOMContentLoaded', async () => {
    initTheme();
    const isAuthenticated = await checkUserSession();
    if (!isAuthenticated) return;

    setupMQTT();
    displayUsername();
    loadNotifications();
    loadUserProfile();
    setupProfileSettings();
    
    // Auto-refresh notifications from database every 30 seconds
    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(() => {
        console.log('⏰ Auto-refreshing notifications...');
        loadNotifications();
    }, 30000);
});

async function checkUserSession() {
    try {
        const response = await fetch('backend/check_session.php', {
            credentials: 'same-origin'
        });

        if (!response.ok) {
            window.location.href = 'login.html';
            return false;
        }

        const data = await response.json();
        if (!data.logged_in) {
            window.location.href = 'login.html';
            return false;
        }

        currentUser = {
            id: data.user_id,
            username: data.username
        };

        localStorage.setItem('user_id', data.user_id);
        localStorage.setItem('username', data.username);
        return true;
    } catch (error) {
        console.error('Session check failed:', error);
        window.location.href = 'login.html';
        return false;
    }
}

function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    applyTheme(savedTheme);
}

function toggleTheme() {
    const nextTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    applyTheme(nextTheme);
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);

    const toggleButton = document.getElementById('themeToggle');
    if (toggleButton) {
        toggleButton.textContent = theme === 'dark' ? '☀️ Light' : '🌙 Dark';
    }
}

function displayUsername() {
    const usernameDisplay = document.getElementById('username-display');
    const sidebarUsername = document.getElementById('sidebar-username');
    const profileAvatar = document.getElementById('profileAvatar');

    if (usernameDisplay) {
        usernameDisplay.textContent = `👤 ${currentUser.username}`;
    }

    if (sidebarUsername) {
        sidebarUsername.textContent = currentUser.username;
    }

    if (profileAvatar) {
        if (currentUser.avatar && currentUser.avatar.trim()) {
            profileAvatar.src = currentUser.avatar;
        } else {
            profileAvatar.src = getLetterAvatar(currentUser.username);
        }
    }
}

function getLetterAvatar(username) {
    const letter = username && username.trim().length > 0 ? username.trim().charAt(0).toUpperCase() : 'U';
    const bg = '#2563eb';
    const fg = '#ffffff';
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect width="100%" height="100%" rx="40" ry="40" fill="${bg}"/><text x="50%" y="50%" dy="0.1em" text-anchor="middle" dominant-baseline="middle" font-family="Segoe UI, sans-serif" font-size="36" font-weight="700" fill="${fg}">${letter}</text></svg>`;
    return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg);
}

function toggleSidebar() {
    const sidebar = document.getElementById('userSidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const dashboardContainer = document.querySelector('.dashboard-container');

    if (!sidebar || !overlay || !dashboardContainer) return;

    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    } else {
        sidebar.classList.toggle('collapsed');
        dashboardContainer.classList.toggle('sidebar-collapsed');
    }
}

function toggleSettings() {
    const settingsSection = document.getElementById('profile-settings');
    const overlay = document.getElementById('profile-settings-overlay');
    if (settingsSection && overlay) {
        settingsSection.classList.toggle('hidden');
        overlay.classList.toggle('hidden');
        if (!settingsSection.classList.contains('hidden')) {
            loadUserProfile();
        }
    }
}

function closeSettings(event) {
    if (event && event.target.id !== 'profile-settings-overlay') return;
    const settingsSection = document.getElementById('profile-settings');
    const overlay = document.getElementById('profile-settings-overlay');
    if (settingsSection && overlay) {
        settingsSection.classList.add('hidden');
        overlay.classList.add('hidden');
    }
}

function setupProfileSettings() {
    const avatarInput = document.getElementById('avatar');
    if (avatarInput) {
        avatarInput.addEventListener('change', previewAvatar);
    }

    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', saveUserProfile);
    }
}

function previewAvatar(event) {
    const file = event.target.files[0];
    if (!file) return;

    const preview = document.getElementById('profilePreview');
    if (preview) {
        preview.src = URL.createObjectURL(file);
    }
}

function loadUserProfile() {
    fetch('backend/user_api.php?action=get_profile', {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user) {
                currentUser = {
                    ...currentUser,
                    ...data.user
                };
                displayUsername();

                const usernameField = document.getElementById('username');
                const emailField = document.getElementById('email');
                const passwordField = document.getElementById('password');
                const profilePreview = document.getElementById('profilePreview');

                if (usernameField) usernameField.value = data.user.username || '';
                if (emailField) emailField.value = data.user.email || '';
                if (passwordField) passwordField.value = '';
                
                if (profilePreview) {
                    if (data.user.avatar && data.user.avatar.trim()) {
                        profilePreview.src = data.user.avatar;
                    } else {
                        profilePreview.src = getLetterAvatar(data.user.username);
                    }
                }
            }
        })
        .catch(error => {
            console.error('Unable to load profile:', error);
        });
}

function saveUserProfile(event) {
    event.preventDefault();
    const form = document.getElementById('profileForm');
    const messageBox = document.getElementById('profileMessage');

    if (!form || !messageBox) return;

    messageBox.textContent = 'Saving profile...';
    messageBox.className = 'message-box';

    const formData = new FormData(form);

    fetch('backend/user_api.php?action=update_profile', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.user) {
                currentUser = {
                    ...currentUser,
                    ...data.user
                };
                localStorage.setItem('username', data.user.username);
                displayUsername();
            }
            messageBox.textContent = '✅ ' + data.message;
            messageBox.className = 'message-box success';
            setTimeout(() => {
                closeSettings();
            }, 1500);
        } else {
            messageBox.textContent = '❌ ' + (data.message || 'Error saving profile');
            messageBox.className = 'message-box error';
        }
    })
    .catch(error => {
        messageBox.textContent = '❌ Unable to save profile. Please try again.';
        messageBox.className = 'message-box error';
        console.error('Profile save error:', error);
    });
}

function setupMQTT() {
    client.on("connect", () => {
        console.log("Connected to MQTT Broker");
        
        client.subscribe("school/announcements");
        client.subscribe("system/alerts");
        client.subscribe("weather/alerts");
    });

    client.on("message", (topic, message) => {
        let payload = {};
        let text = message.toString();

        try {
            payload = JSON.parse(text);
            text = payload.message || text;
        } catch (e) {
            payload = {};
        }

        const notification = {
            id: Date.now(),
            alert_id: payload.alert_id || 0,
            topic: topic,
            message: text,
            time: new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            }),
            timestamp: new Date(),
            read: false,
            notification_id: null
        };

        allNotifications.unshift(notification);
        renderNotifications();
        saveNotification(notification);
        showBadgePopup(notification);
    });

    client.on("error", (err) => {
        console.error("MQTT Error:", err);
    });

    client.on("disconnect", () => {
        console.log("Disconnected from MQTT Broker");
    });
}

function loadNotifications() {
    const container = document.getElementById('notifications');
    const emptyState = document.getElementById('empty-state');
    const loadingState = document.getElementById('loading-state');

    // Reset UI state
    if (container) container.innerHTML = '';
    if (emptyState) emptyState.classList.add('hidden');
    if (loadingState) loadingState.classList.remove('hidden');

    console.log('🔄 Loading notifications from database...');

    fetch('backend/notification_api.php?action=get_user_notifications', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => {
            console.log('📨 Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('📦 Received data:', data);
            
            if (data.success && Array.isArray(data.notifications)) {
                allNotifications = data.notifications.map(n => ({
                    id: n.notification_id || Date.now(),
                    alert_id: n.alert_id || 0,
                    topic: n.topic || 'General',
                    message: n.message || '(No message)',
                    time: new Date(n.created_at).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    }),
                    timestamp: new Date(n.created_at),
                    read: n.is_read == 1,
                    notification_id: n.notification_id,
                    read_at: n.read_at
                }));
                console.log(`✅ Loaded ${allNotifications.length} notifications from database`);
            } else {
                console.warn('⚠️ Failed to load notifications:', data.message || 'Invalid response');
                allNotifications = [];
            }

            if (loadingState) loadingState.classList.add('hidden');
            renderNotifications();
        })
        .catch(error => {
            console.error('❌ Error loading notifications:', error);
            if (loadingState) loadingState.classList.add('hidden');
            allNotifications = [];
            renderNotifications();
        });
}

function saveNotification(notification) {
    fetch('backend/notification_api.php?action=save_notification', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            alert_id: notification.alert_id,
            topic: notification.topic,
            message: notification.message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.notification_id) {
            notification.notification_id = data.notification_id;
        }
    })
    .catch(error => {
        console.error('Error saving notification:', error);
    });
}

function renderNotifications() {
    const container = document.getElementById('notifications');
    const emptyState = document.getElementById('empty-state');
    const loadingState = document.getElementById('loading-state');

    let filteredNotifications = allNotifications;
    if (currentFilter !== 'all') {
        filteredNotifications = allNotifications.filter(n => n.topic === currentFilter);
    }

    if (loadingState) {
        loadingState.classList.add('hidden');
    }

    if (filteredNotifications.length === 0) {
        container.innerHTML = '';
        if (emptyState) {
            emptyState.classList.remove('hidden');
        }
        return;
    }

    if (emptyState) {
        emptyState.classList.add('hidden');
    }
    container.innerHTML = filteredNotifications.map((notif) => {
        const actualIndex = allNotifications.indexOf(notif);
        return `
        <div class="badge ${notif.read ? '' : 'unread'}">
            <button class="badge-delete" onclick="deleteNotification(${actualIndex})">×</button>
            <div class="badge-header">
                <span class="badge-topic">${getTopicEmoji(notif.topic)} ${notif.topic}</span>
                <span class="badge-time">${notif.time}</span>
            </div>
            <div class="badge-message">${escapeHtml(notif.message)}</div>
            <div class="badge-actions">
                <button class="badge-action-btn" onclick="toggleRead(${actualIndex})">
                    ${notif.read ? 'Mark Unread' : 'Mark Read'}
                </button>
            </div>
        </div>
    `;
    }).join('');
}

function getTopicEmoji(topic) {
    const emojis = {
        'school/announcements': '🎓',
        'system/alerts': '⚙️',
        'weather/alerts': '🌤️'
    };
    return emojis[topic] || '📢';
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function filterTopic(topic, button) {
    currentFilter = topic;
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    if (button) button.classList.add('active');
    renderNotifications();
}

function deleteNotification(index) {
    if (confirm('Delete this notification?')) {
        allNotifications.splice(index, 1);
        renderNotifications();
    }
}

function toggleRead(index) {
    const notification = allNotifications[index];
    const newRead = !notification.read;
    const alertId = notification.alert_id || 0;
    const notificationId = notification.notification_id || 0;

    fetch('backend/notification_api.php?action=toggle_read', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            notification_id: notificationId,
            alert_id: alertId,
            is_read: newRead ? 1 : 0
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notification.read = newRead;
            renderNotifications();
        } else {
            console.error('Failed to update read status:', data.message);
        }
    })
    .catch(error => {
        console.error('Error updating read status:', error);
    });
}

function showBadgePopup(notification) {
    const popup = document.createElement('div');
    popup.className = 'popup-toast';
    popup.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <span>${getTopicEmoji(notification.topic)}</span>
            <div>
                <strong>${notification.topic.split('/')[0].toUpperCase()}</strong>
                <p style="margin: 5px 0 0 0; font-size: 14px;">${notification.message.substring(0, 50)}...</p>
            </div>
        </div>
    `;

    popup.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        animation: slideUp 0.5s ease;
        z-index: 1000;
        max-width: 350px;
    `;

    document.body.appendChild(popup);
    playNotificationSound();

    setTimeout(() => {
        popup.style.animation = 'slideDown 0.5s ease';
        setTimeout(() => popup.remove(), 500);
    }, 5000);
}

function playNotificationSound() {
    const audio = new Audio('https://notificationsounds.com/storage/sounds/file-sounds-1150-pristine.mp3');
    audio.volume = 0.5;
    audio.play().catch(e => console.log('Audio play failed:', e));
}

function logout() {
    if (!confirm('Are you sure you want to logout?')) return;

    if (refreshInterval) clearInterval(refreshInterval);
    
    fetch('backend/logout.php', {
        method: 'POST',
        credentials: 'same-origin'
    }).finally(() => {
        localStorage.removeItem('user_id');
        localStorage.removeItem('username');
        localStorage.removeItem('role');
        client.end();
        window.location.href = 'login.html';
    });
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from { transform: translateY(100px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    @keyframes slideDown {
        from { transform: translateY(0); opacity: 1; }
        to { transform: translateY(100px); opacity: 0; }
    }
`;
document.head.appendChild(style);

