// Admin API Configuration
const ADMIN_API_BASE_URL = 'admin-backend.php';

// API Helper Functions
async function adminApiCall(action, method = 'GET', data = null) {
    try {
        const url = `${ADMIN_API_BASE_URL}?action=${action}`;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(url, options);
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || 'Network error');
        }
        
        return result;
    } catch (error) {
        console.error('Admin API Error:', error);
        throw error;
    }
}

// Sample data for admin dashboard
const sampleUsers = [
    {
        id: 1,
        username: "Sarah Johnson",
        email: "sarah.j@email.com",
        gender: "female",
        age: 28,
        status: "active",
        joined: "2024-01-15",
        profile_pic: "https://images.unsplash.com/photo-1494790108755-2616b612b786?w=50&h=50&fit=crop&crop=face"
    },
    {
        id: 2,
        username: "Michael Chen",
        email: "michael.c@email.com",
        gender: "male",
        age: 32,
        status: "active",
        joined: "2024-01-10",
        profile_pic: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=50&h=50&fit=crop&crop=face"
    },
    {
        id: 3,
        username: "Emma Wilson",
        email: "emma.w@email.com",
        gender: "female",
        age: 26,
        status: "active",
        joined: "2024-01-08",
        profile_pic: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=50&h=50&fit=crop&crop=face"
    },
    {
        id: 4,
        username: "David Brown",
        email: "david.b@email.com",
        gender: "male",
        age: 30,
        status: "banned",
        joined: "2024-01-05",
        profile_pic: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=50&h=50&fit=crop&crop=face"
    },
    {
        id: 5,
        username: "Jessica Lee",
        email: "jessica.l@email.com",
        gender: "female",
        age: 27,
        status: "active",
        joined: "2024-01-03",
        profile_pic: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=50&h=50&fit=crop&crop=face"
    }
];

const sampleReports = [
    {
        id: 1,
        reported_user: "David Brown",
        reporter: "Sarah Johnson",
        reason: "Inappropriate messages",
        status: "pending",
        date: "2024-01-20"
    },
    {
        id: 2,
        reported_user: "Alex Smith",
        reporter: "Emma Wilson",
        reason: "Fake profile",
        status: "reviewed",
        date: "2024-01-18"
    },
    {
        id: 3,
        reported_user: "Mike Johnson",
        reporter: "Jessica Lee",
        reason: "Harassment",
        status: "pending",
        date: "2024-01-17"
    }
];

const sampleMessages = [
    {
        id: 1,
        sender: "Sarah Johnson",
        receiver: "Michael Chen",
        message: "Hi! I really enjoyed your profile. Would you like to grab coffee sometime?",
        date: "2024-01-20 14:30"
    },
    {
        id: 2,
        sender: "Emma Wilson",
        receiver: "David Brown",
        message: "Thanks for the message! I'd love to meet up.",
        date: "2024-01-20 13:15"
    },
    {
        id: 3,
        sender: "Michael Chen",
        receiver: "Sarah Johnson",
        message: "That sounds great! How about tomorrow at 3 PM?",
        date: "2024-01-20 15:00"
    }
];

const sampleVerifications = [
    {
        id: 1,
        user: "Sarah Johnson",
        selfie: "https://images.unsplash.com/photo-1494790108755-2616b612b786?w=100&h=100&fit=crop&crop=face",
        status: "verified",
        submitted: "2024-01-15"
    },
    {
        id: 2,
        user: "Michael Chen",
        selfie: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face",
        status: "pending",
        submitted: "2024-01-20"
    },
    {
        id: 3,
        user: "Emma Wilson",
        selfie: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face",
        status: "rejected",
        submitted: "2024-01-18"
    }
];

// Navigation functionality
function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Show selected section
    document.getElementById(sectionId).classList.add('active');
    
    // Update navigation active state
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    event.target.closest('.nav-item').classList.add('active');
    
    // Update page title
    const pageTitle = document.getElementById('page-title');
    const titles = {
        'dashboard': 'Dashboard',
        'users': 'User Management',
        'reports': 'User Reports',
        'messages': 'Message Monitoring',
        'verifications': 'Profile Verifications',
        'settings': 'System Settings'
    };
    pageTitle.textContent = titles[sectionId];
    
    // Load section-specific data
    loadSectionData(sectionId);
}

function loadSectionData(sectionId) {
    switch(sectionId) {
        case 'dashboard':
            loadDashboardStats();
            break;
        case 'users':
            loadUsersTable();
            break;
        case 'reports':
            loadReportsTable();
            break;
        case 'messages':
            loadMessagesTable();
            break;
        case 'verifications':
            loadVerificationsTable();
            break;
    }
}

// Sidebar toggle for mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// Initialize charts
function initializeCharts() {
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'New Users',
                data: [1200, 1900, 3000, 5000, 8000, 12847],
                borderColor: '#e91e63',
                backgroundColor: 'rgba(233, 30, 99, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f0f0f0'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Gender Distribution Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Female', 'Male', 'Other'],
            datasets: [{
                data: [45, 50, 5],
                backgroundColor: [
                    '#e91e63',
                    '#2196f3',
                    '#9c27b0'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Dashboard Statistics
async function loadDashboardStats() {
    try {
        const result = await adminApiCall('stats');
        
        if (result.success) {
            updateDashboardStats(result.stats);
        } else {
            console.error('Failed to load stats:', result.error);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

function updateDashboardStats(stats) {
    // Update stat cards with real data
    const statCards = document.querySelectorAll('.stat-card');
    
    if (statCards.length >= 4) {
        // Total Users
        const totalUsersCard = statCards[0].querySelector('.stat-number');
        if (totalUsersCard) {
            totalUsersCard.textContent = stats.users.total_users || 0;
        }
        
        // Active Matches (you can calculate this based on likes)
        const activeMatchesCard = statCards[1].querySelector('.stat-number');
        if (activeMatchesCard) {
            activeMatchesCard.textContent = Math.floor(stats.users.total_users * 0.3) || 0;
        }
        
        // Reports
        const reportsCard = statCards[2].querySelector('.stat-number');
        if (reportsCard) {
            reportsCard.textContent = stats.reports.total_reports || 0;
        }
        
        // Messages
        const messagesCard = statCards[3].querySelector('.stat-number');
        if (messagesCard) {
            messagesCard.textContent = stats.messages.total_messages || 0;
        }
    }
}

// Table loading functions
async function loadUsersTable() {
    try {
        const result = await adminApiCall('users');
        
        if (result.success) {
            displayUsersTable(result.users);
        } else {
            console.error('Failed to load users:', result.error);
            displayUsersTable(sampleUsers);
        }
    } catch (error) {
        console.error('Error loading users:', error);
        displayUsersTable(sampleUsers);
    }
}

function displayUsersTable(users) {
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${user.id}</td>
            <td>
                <div class="user-profile">
                    <img src="${user.profile_pic || 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=50&h=50&fit=crop&crop=face'}" alt="${user.username}">
                    <span>${user.username}</span>
                </div>
            </td>
            <td>${user.username}</td>
            <td>${user.email}</td>
            <td>${user.gender.charAt(0).toUpperCase() + user.gender.slice(1)}</td>
            <td>${user.age}</td>
            <td>
                <span class="status-badge status-${user.status}">
                    ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                </span>
            </td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <button class="btn btn-secondary" onclick="editUser(${user.id})">
                    <i class="fas fa-edit"></i>
                </button>
                ${user.status === 'active' ? 
                    `<button class="btn btn-danger" onclick="banUser(${user.id})">
                        <i class="fas fa-ban"></i>
                    </button>` :
                    `<button class="btn btn-primary" onclick="unbanUser(${user.id})">
                        <i class="fas fa-check"></i>
                    </button>`
                }
                <button class="btn btn-danger" onclick="deleteUser(${user.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

async function loadReportsTable() {
    try {
        const result = await adminApiCall('reports');
        
        if (result.success) {
            displayReportsTable(result.reports);
        } else {
            console.error('Failed to load reports:', result.error);
            displayReportsTable(sampleReports);
        }
    } catch (error) {
        console.error('Error loading reports:', error);
        displayReportsTable(sampleReports);
    }
}

function displayReportsTable(reports) {
    const tbody = document.getElementById('reportsTableBody');
    tbody.innerHTML = reports.map(report => `
        <tr>
            <td>${report.id}</td>
            <td>${report.reported_user_name || report.reported_user}</td>
            <td>${report.reporter_name || report.reporter}</td>
            <td>${report.reason}</td>
            <td>
                <span class="status-badge status-${report.status}">
                    ${report.status.charAt(0).toUpperCase() + report.status.slice(1)}
                </span>
            </td>
            <td>${formatDate(report.created_at)}</td>
            <td>
                ${report.status === 'pending' ? 
                    `<button class="btn btn-primary" onclick="resolveReport(${report.id})">
                        <i class="fas fa-check"></i> Resolve
                    </button>` : ''
                }
                <button class="btn btn-secondary" onclick="reviewReport(${report.id})">
                    <i class="fas fa-eye"></i> Review
                </button>
                <button class="btn btn-danger" onclick="deleteReport(${report.id})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        </tr>
    `).join('');
}

async function loadMessagesTable() {
    try {
        const result = await adminApiCall('messages');
        
        if (result.success) {
            displayMessagesTable(result.messages);
        } else {
            console.error('Failed to load messages:', result.error);
            displayMessagesTable(sampleMessages);
        }
    } catch (error) {
        console.error('Error loading messages:', error);
        displayMessagesTable(sampleMessages);
    }
}

function displayMessagesTable(messages) {
    const tbody = document.getElementById('messagesTableBody');
    tbody.innerHTML = messages.map(message => `
        <tr>
            <td>${message.id}</td>
            <td>${message.sender_name || message.sender}</td>
            <td>${message.receiver_name || message.receiver}</td>
            <td>${truncateText(message.message, 50)}</td>
            <td>${formatDateTime(message.sent_at)}</td>
            <td>
                <button class="btn btn-secondary" onclick="viewMessage(${message.id})">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="btn btn-danger" onclick="deleteMessage(${message.id})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        </tr>
    `).join('');
}

async function loadVerificationsTable() {
    try {
        const result = await adminApiCall('verifications');
        
        if (result.success) {
            displayVerificationsTable(result.verifications);
        } else {
            console.error('Failed to load verifications:', result.error);
            displayVerificationsTable(sampleVerifications);
        }
    } catch (error) {
        console.error('Error loading verifications:', error);
        displayVerificationsTable(sampleVerifications);
    }
}

function displayVerificationsTable(verifications) {
    const tbody = document.getElementById('verificationsTableBody');
    tbody.innerHTML = verifications.map(verification => `
        <tr>
            <td>${verification.id}</td>
            <td>${verification.user_name || verification.user}</td>
            <td>
                <img src="${verification.selfie}" alt="Selfie" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
            </td>
            <td>
                <span class="status-badge status-${verification.status}">
                    ${verification.status.charAt(0).toUpperCase() + verification.status.slice(1)}
                </span>
            </td>
            <td>${formatDate(verification.submitted_at)}</td>
            <td>
                ${verification.status === 'pending' ? `
                    <button class="btn btn-primary" onclick="approveVerification(${verification.id})">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="btn btn-danger" onclick="rejectVerification(${verification.id})">
                        <i class="fas fa-times"></i> Reject
                    </button>
                ` : `
                    <button class="btn btn-secondary" onclick="viewVerification(${verification.id})">
                        <i class="fas fa-eye"></i> View
                    </button>
                `}
            </td>
        </tr>
    `).join('');
}

// CRUD Operations
async function createUser() {
    // This would open a modal for creating a new user
    alert('Create user modal would open here');
}

async function editUser(userId) {
    try {
        const result = await adminApiCall(`user&id=${userId}`);
        
        if (result.success) {
            // This would open an edit modal with user data
            alert(`Edit user: ${result.user.username}\n\nEdit modal would open here with user data.`);
        } else {
            alert('Failed to load user data');
        }
    } catch (error) {
        alert('Error loading user data: ' + error.message);
    }
}

async function updateUser(userId, userData) {
    try {
        const result = await adminApiCall('update_user', 'PUT', { id: userId, ...userData });
        
        if (result.success) {
            alert('User updated successfully!');
            loadUsersTable(); // Refresh table
        } else {
            alert('Failed to update user: ' + result.error);
        }
    } catch (error) {
        alert('Error updating user: ' + error.message);
    }
}

async function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        try {
            const result = await adminApiCall(`delete_user&id=${userId}`, 'DELETE');
            
            if (result.success) {
                alert('User deleted successfully!');
                loadUsersTable(); // Refresh table
            } else {
                alert('Failed to delete user: ' + result.error);
            }
        } catch (error) {
            alert('Error deleting user: ' + error.message);
        }
    }
}

async function banUser(userId) {
    if (confirm('Are you sure you want to ban this user?')) {
        try {
            const result = await adminApiCall('ban_user', 'POST', { user_id: userId });
            
            if (result.success) {
                alert('User banned successfully!');
                loadUsersTable(); // Refresh table
            } else {
                alert('Failed to ban user: ' + result.error);
            }
        } catch (error) {
            alert('Error banning user: ' + error.message);
        }
    }
}

async function unbanUser(userId) {
    if (confirm('Are you sure you want to unban this user?')) {
        try {
            const result = await adminApiCall('unban_user', 'POST', { user_id: userId });
            
            if (result.success) {
                alert('User unbanned successfully!');
                loadUsersTable(); // Refresh table
            } else {
                alert('Failed to unban user: ' + result.error);
            }
        } catch (error) {
            alert('Error unbanning user: ' + error.message);
        }
    }
}

async function resolveReport(reportId) {
    if (confirm('Mark this report as resolved?')) {
        try {
            const result = await adminApiCall('resolve_report', 'POST', { report_id: reportId });
            
            if (result.success) {
                alert('Report resolved successfully!');
                loadReportsTable(); // Refresh table
            } else {
                alert('Failed to resolve report: ' + result.error);
            }
        } catch (error) {
            alert('Error resolving report: ' + error.message);
        }
    }
}

async function deleteReport(reportId) {
    if (confirm('Are you sure you want to delete this report?')) {
        try {
            const result = await adminApiCall(`delete_report&id=${reportId}`, 'DELETE');
            
            if (result.success) {
                alert('Report deleted successfully!');
                loadReportsTable(); // Refresh table
            } else {
                alert('Failed to delete report: ' + result.error);
            }
        } catch (error) {
            alert('Error deleting report: ' + error.message);
        }
    }
}

async function approveVerification(verificationId) {
    if (confirm('Approve this verification?')) {
        try {
            const result = await adminApiCall('approve_verification', 'POST', { verification_id: verificationId });
            
            if (result.success) {
                alert('Verification approved successfully!');
                loadVerificationsTable(); // Refresh table
            } else {
                alert('Failed to approve verification: ' + result.error);
            }
        } catch (error) {
            alert('Error approving verification: ' + error.message);
        }
    }
}

async function rejectVerification(verificationId) {
    if (confirm('Reject this verification?')) {
        try {
            const result = await adminApiCall('reject_verification', 'POST', { verification_id: verificationId });
            
            if (result.success) {
                alert('Verification rejected successfully!');
                loadVerificationsTable(); // Refresh table
            } else {
                alert('Failed to reject verification: ' + result.error);
            }
        } catch (error) {
            alert('Error rejecting verification: ' + error.message);
        }
    }
}

async function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        try {
            const result = await adminApiCall('delete_message', 'POST', { message_id: messageId });
            
            if (result.success) {
                alert('Message deleted successfully!');
                loadMessagesTable(); // Refresh table
            } else {
                alert('Failed to delete message: ' + result.error);
            }
        } catch (error) {
            alert('Error deleting message: ' + error.message);
        }
    }
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function truncateText(text, maxLength) {
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
}

// Other action functions (for compatibility)
function reviewReport(reportId) {
    console.log('Review report:', reportId);
    alert('Review report functionality would open a modal here');
}

function viewMessage(messageId) {
    console.log('View message:', messageId);
    alert('View message functionality would open a modal here');
}

function viewVerification(verificationId) {
    console.log('View verification:', verificationId);
    alert('View verification functionality would open a modal here');
}

function openUserModal() {
    alert('Add user modal would open here');
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        // In a real app, this would clear session and redirect
        window.location.href = 'index.html';
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
    
    // Load initial data
    loadDashboardStats();
    loadUsersTable();
    
    // Add search functionality
    const userSearch = document.getElementById('userSearch');
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#usersTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    // Add status filter functionality
    const userStatus = document.getElementById('userStatus');
    if (userStatus) {
        userStatus.addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('#usersTableBody tr');
            
            rows.forEach(row => {
                const statusCell = row.querySelector('.status-badge');
                if (status && statusCell) {
                    const userStatus = statusCell.textContent.toLowerCase();
                    row.style.display = userStatus.includes(status) ? '' : 'none';
                } else {
                    row.style.display = '';
                }
            });
        });
    }
    
    // Add gender filter functionality
    const userGender = document.getElementById('userGender');
    if (userGender) {
        userGender.addEventListener('change', function() {
            const gender = this.value;
            const rows = document.querySelectorAll('#usersTableBody tr');
            
            rows.forEach(row => {
                const genderCell = row.cells[4]; // Gender column
                if (gender && genderCell) {
                    const userGender = genderCell.textContent.toLowerCase();
                    row.style.display = userGender.includes(gender) ? '' : 'none';
                } else {
                    row.style.display = '';
                }
            });
        });
    }
}); 