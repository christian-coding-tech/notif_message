// Initialize MQTT Client for Admin
const client = mqtt.connect("ws://localhost:9001");

let currentUser = {};
let currentFilter = 'all';

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    checkUserSession();
    setupMQTT();
    displayUserInfo();
    loadDashboard();
    setupCharacterCounter();
    setupMessageListener();
});

// ===== THEME TOGGLE =====
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

// ===== AUTHENTICATION =====
function checkUserSession() {
    const userId = localStorage.getItem('user_id');
    const username = localStorage.getItem('username');
    const role = localStorage.getItem('role');
    
    if (!userId || !username) {
        window.location.href = 'login.html';
        return;
    }
    
    // Check if admin
    if (role !== 'admin') {
        alert('Access Denied! Only admins can access this panel.');
        window.location.href = 'index.html';
        return;
    }
    
    currentUser = {
        id: userId,
        username: username,
        role: role
    };
}

function displayUserInfo() {
    const usernameDisplay = document.getElementById('username-display');
    const sidebarUsername = document.getElementById('sidebar-username');
    
    if (usernameDisplay) {
        usernameDisplay.textContent = `👤 ${currentUser.username}`;
    }
    
    if (sidebarUsername) {
        sidebarUsername.textContent = currentUser.username;
    }
}

// ===== MQTT SETUP =====
function setupMQTT() {
    client.on("connect", () => {
        console.log("Admin Connected to MQTT Broker");
    });

    client.on("error", (err) => {
        console.error("MQTT Error:", err);
    });

    client.on("disconnect", () => {
        console.log("Disconnected from MQTT Broker");
    });
}

// ===== SECTION NAVIGATION =====
function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.admin-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active class from nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Show selected section
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.add('active');
        
        // Add active class to corresponding nav item
        document.querySelector(`a[href="#${sectionId}"]`).classList.add('active');
        
        // Load section-specific data
        if (sectionId === 'dashboard') {
            loadDashboard();
        } else if (sectionId === 'manage-alerts') {
            loadAlerts('all');
        } else if (sectionId === 'manage-users') {
            loadUsers();
        }
    }
    
    // Close sidebar on mobile
    if (window.innerWidth <= 768) {
        toggleSidebar();
    }
}

// ===== SIDEBAR TOGGLE =====
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const adminContainer = document.querySelector('.admin-container');

    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    } else {
        sidebar.classList.toggle('collapsed');
        adminContainer.classList.toggle('sidebar-collapsed');
    }
}

// ===== DASHBOARD =====
function loadDashboard() {
    fetchAlerts('all').then(data => {
        if (data.success) {
            const alerts = data.alerts;
            
            // Calculate stats
            const activeCount = alerts.filter(a => a.status === 'active').length;
            const totalCount = alerts.length;
            const archivedCount = alerts.filter(a => a.status === 'archived').length;
            
            // Update counters
            document.getElementById('active-alerts-count').textContent = activeCount;
            document.getElementById('total-alerts-count').textContent = totalCount;
            document.getElementById('archived-alerts-count').textContent = archivedCount;
            
            // Show recent alerts
            displayRecentAlerts(alerts.slice(0, 5));
        }
    });
}

function displayRecentAlerts(alerts) {
    const container = document.getElementById('recent-alerts-list');
    
    if (alerts.length === 0) {
        container.innerHTML = '<p class="no-data">No alerts created yet. <a href="#create-alert" onclick="showSection(\'create-alert\')">Create one now</a></p>';
        return;
    }
    
    let html = '<table class="alerts-table"><thead><tr>';
    html += '<th>Message</th><th>Type</th><th>Status</th><th>Created</th></tr></thead><tbody>';
    
    alerts.forEach(alert => {
        const date = new Date(alert.created_at).toLocaleDateString();
        const typeIcon = getAlertTypeIcon(alert.alert_type);
        const statusBadge = `<span class="status-badge ${alert.status}">${alert.status}</span>`;
        
        html += `<tr>
            <td><strong>${alert.message.substring(0, 50)}...</strong></td>
            <td>${typeIcon}</td>
            <td>${statusBadge}</td>
            <td>${date}</td>
        </tr>`;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

// ===== CREATE ALERT =====
function setupCharacterCounter() {
    const messageInput = document.getElementById('message');
    const charCount = document.getElementById('char-count');
    
    messageInput.addEventListener('input', () => {
        charCount.textContent = `${messageInput.value.length} / 500 characters`;
    });
}

function createAlert() {
    const topic = document.getElementById('topic').value;
    const message = document.getElementById('message').value;
    const alertType = document.querySelector('input[name="alert-type"]:checked').value;
    const messageBox = document.getElementById('adminMessage');
    
    if (!topic) {
        showMessage('Please select a topic', 'error', messageBox);
        return;
    }
    
    if (message.trim() === '') {
        showMessage('Please enter a message', 'error', messageBox);
        return;
    }
    
    if (message.length > 500) {
        showMessage('Message cannot exceed 500 characters', 'error', messageBox);
        return;
    }
    
    // Show loading state
    showMessage('Creating alert...', 'loading', messageBox);
    
    // Save to database
    fetch('backend/admin_api.php?action=create_alert', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            topic: topic,
            message: message,
            alert_type: alertType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Publish to MQTT
            client.publish(topic, JSON.stringify({
                message: message,
                type: alertType,
                timestamp: new Date().toISOString(),
                admin: currentUser.username,
                alert_id: data.alert_id
            }), (err) => {
                if (err) {
                    showMessage('❌ Failed to broadcast alert via MQTT', 'error', messageBox);
                } else {
                    showMessage('✅ Alert sent successfully!', 'success', messageBox);
                    clearAlertForm();
                    loadDashboard();
                }
            });
        } else {
            showMessage('❌ ' + data.message, 'error', messageBox);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('❌ Error creating alert', 'error', messageBox);
    });
}

function clearAlertForm() {
    document.getElementById('topic').value = '';
    document.getElementById('message').value = '';
    document.querySelector('input[name="alert-type"][value="announcement"]').checked = true;
    document.getElementById('char-count').textContent = '0 / 500 characters';
    document.getElementById('adminMessage').innerHTML = '';
}

// ===== MANAGE ALERTS =====
function filterAlerts(status, button) {
    currentFilter = status;
    
    // Update button states
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    if (button) {
        button.classList.add('active');
    }
    
    loadAlerts(status);
}

function loadAlerts(status = 'all') {
    fetchAlerts(status).then(data => {
        if (data.success) {
            displayAlerts(data.alerts);
        }
    });
}

function displayAlerts(alerts) {
    const container = document.getElementById('alerts-list');
    
    if (alerts.length === 0) {
        container.innerHTML = '<p class="no-data">No alerts found</p>';
        return;
    }
    
    let html = '<table class="alerts-table"><thead><tr>';
    html += '<th>Message</th><th>Type</th><th>Topic</th><th>Status</th><th>Created By</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
    
    alerts.forEach(alert => {
        const date = new Date(alert.created_at).toLocaleDateString();
        const typeIcon = getAlertTypeIcon(alert.alert_type);
        const statusBadge = `<span class="status-badge ${alert.status}">${alert.status}</span>`;
        const topicLabel = getTopicLabel(alert.topic);
        
        html += `<tr>
            <td><strong>${alert.message.substring(0, 40)}</strong>...</td>
            <td>${typeIcon}</td>
            <td>${topicLabel}</td>
            <td>${statusBadge}</td>
            <td>${alert.username}</td>
            <td>${date}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon" title="View" onclick="viewAlert(${alert.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon" title="Readers" onclick="viewReaders(${alert.id})">
                        <i class="fas fa-users"></i>
                    </button>
                    <button class="btn-icon" title="Archive" onclick="toggleArchive(${alert.id}, '${alert.status}')">
                        <i class="fas fa-archive"></i>
                    </button>
                    <button class="btn-icon delete" title="Delete" onclick="deleteAlert(${alert.id})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function viewAlert(alertId) {
    const selected = allAlerts.find(a => a.id === alertId);
    if (selected) {
        const message = `Alert Details\n\nMessage: ${selected.message}\nType: ${selected.alert_type}\nTopic: ${selected.topic}\nCreated: ${new Date(selected.created_at).toLocaleString()}`;
        window.alert(message);
    }
}

function viewReaders(alertId) {
    fetch(`backend/admin_api.php?action=get_alert_readers&alert_id=${alertId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                window.alert('Unable to load readers: ' + data.message);
                return;
            }

            if (data.readers.length === 0) {
                window.alert('No users have marked this alert as read yet.');
                return;
            }

            const lines = data.readers.map(reader => {
                const status = reader.is_read == 1 ? 'Read' : 'Unread';
                const time = reader.read_at ? new Date(reader.read_at).toLocaleString() : 'not read yet';
                return `${reader.username}: ${status} (${time})`;
            });

            window.alert('Read status for this alert:\n\n' + lines.join('\n'));
        })
        .catch(error => {
            console.error('Error fetching readers:', error);
            window.alert('Error loading reader details.');
        });
}

function toggleArchive(alertId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'archived' : 'active';
    
    fetch('backend/admin_api.php?action=update_alert_status', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            alert_id: alertId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadAlerts(currentFilter);
            loadDashboard();
        } else {
            alert('Error updating alert: ' + data.message);
        }
    });
}

function deleteAlert(alertId) {
    if (!confirm('Are you sure you want to delete this alert?')) {
        return;
    }
    
    fetch('backend/admin_api.php?action=delete_alert', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            alert_id: alertId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadAlerts(currentFilter);
            loadDashboard();
        } else {
            alert('Error deleting alert: ' + data.message);
        }
    });
}

// ===== UTILITY FUNCTIONS =====
function fetchAlerts(status = 'all') {
    return fetch(`backend/admin_api.php?action=get_alerts&status=${status}`)
        .then(response => response.json());
}

let allAlerts = [];
function setupMessageListener() {
    fetchAlerts('all').then(data => {
        if (data.success) {
            allAlerts = data.alerts;
        }
    });
}

function showMessage(text, type, container) {
    container.textContent = text;
    container.className = `message-box ${type}`;
    
    if (type === 'success' || type === 'error') {
        setTimeout(() => {
            container.textContent = '';
            container.className = 'message-box';
        }, 3000);
    }
}

function getAlertTypeIcon(type) {
    switch(type) {
        case 'announcement': return '📢';
        case 'warning': return '⚠️';
        case 'emergency': return '🚨';
        default: return '📢';
    }
}

function getTopicLabel(topic) {
    const labels = {
        'school/announcements': '📚 School',
        'system/alerts': '⚙️ System',
        'weather/alerts': '🌦️ Weather'
    };
    return labels[topic] || topic;
}

// ===== LOGOUT =====
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        localStorage.removeItem('user_id');
        localStorage.removeItem('username');
        localStorage.removeItem('role');
        client.end();
        window.location.href = 'login.html';
    }
}

// Handle Enter key to send message
document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key === 'Enter') {
        const messageInput = document.getElementById('message');
        if (messageInput === document.activeElement) {
            createAlert();
        }
    }
});
