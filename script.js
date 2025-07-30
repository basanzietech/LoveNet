// Sample user data (in a real app, this would come from a database)
const sampleUsers = [
    {
        id: 1,
        username: "Sarah",
        age: 28,
        gender: "female",
        location: "New York, NY",
        bio: "Adventure seeker and coffee enthusiast. Looking for someone to explore the world with!",
        profile_pic: "https://images.unsplash.com/photo-1494790108755-2616b612b786?w=300&h=400&fit=crop&crop=face"
    },
    {
        id: 2,
        username: "Michael",
        age: 32,
        gender: "male",
        location: "Los Angeles, CA",
        bio: "Music producer and dog lover. Passionate about creating meaningful connections.",
        profile_pic: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=face"
    },
    {
        id: 3,
        username: "Emma",
        age: 26,
        gender: "female",
        location: "Chicago, IL",
        bio: "Art teacher and yoga instructor. Love spending time outdoors and trying new restaurants.",
        profile_pic: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=300&h=400&fit=crop&crop=face"
    },
    {
        id: 4,
        username: "David",
        age: 30,
        gender: "male",
        location: "Miami, FL",
        bio: "Software engineer who loves surfing and cooking. Looking for someone to share life's adventures.",
        profile_pic: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=400&fit=crop&crop=face"
    },
    {
        id: 5,
        username: "Jessica",
        age: 27,
        gender: "female",
        location: "Seattle, WA",
        bio: "Bookworm and nature lover. Enjoy hiking, photography, and deep conversations.",
        profile_pic: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=300&h=400&fit=crop&crop=face"
    },
    {
        id: 6,
        username: "Alex",
        age: 29,
        gender: "male",
        location: "Austin, TX",
        bio: "Entrepreneur and fitness enthusiast. Love traveling and meeting new people.",
        profile_pic: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=400&fit=crop&crop=face"
    }
];

// API Configuration
const API_BASE_URL = 'backend.php';

// Modal functionality
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

function switchModal(fromModal, toModal) {
    closeModal(fromModal);
    openModal(toModal);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            closeModal(modal.id);
        }
    });
}

// API Helper Functions
async function apiCall(action, method = 'GET', data = null) {
    try {
        const url = `${API_BASE_URL}?action=${action}`;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (data && method === 'POST') {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(url, options);
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || 'Network error');
        }
        
        return result;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Authentication handlers
async function handleLogin(event) {
    event.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    // Basic validation
    if (!email || !password) {
        alert('Please fill in all fields');
        return;
    }
    
    try {
        const result = await apiCall('login', 'POST', { email, password });
        
        if (result.success) {
            alert('Login successful! Welcome back to LoveNet!');
            closeModal('loginModal');
            
            // Clear form
            document.getElementById('loginEmail').value = '';
            document.getElementById('loginPassword').value = '';
            
            // Store user data in localStorage
            localStorage.setItem('user', JSON.stringify(result.user));
            
            // Show user dashboard or redirect
            showUserDashboard(result.user);
        } else {
            alert(result.error || 'Login failed');
        }
    } catch (error) {
        alert('Login failed: ' + error.message);
    }
}

async function handleRegister(event) {
    event.preventDefault();
    const username = document.getElementById('registerUsername').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const gender = document.getElementById('registerGender').value;
    const dob = document.getElementById('registerDob').value;
    const bio = document.getElementById('registerBio').value;
    
    // Basic validation
    if (!username || !email || !password || !gender || !dob) {
        alert('Please fill in all required fields');
        return;
    }
    
    if (password.length < 6) {
        alert('Password must be at least 6 characters long');
        return;
    }
    
    // Check if user is at least 18 years old
    const birthDate = new Date(dob);
    const today = new Date();
    const age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (age < 18 || (age === 18 && monthDiff < 0)) {
        alert('You must be at least 18 years old to register');
        return;
    }
    
    try {
        const result = await apiCall('register', 'POST', {
            username,
            email,
            password,
            gender,
            dob,
            bio
        });
        
        if (result.success) {
            alert('Registration successful! Welcome to LoveNet! 🎉\n\nYour account has been created and you can now start connecting with other singles.');
            closeModal('registerModal');
            
            // Clear form
            document.getElementById('registerUsername').value = '';
            document.getElementById('registerEmail').value = '';
            document.getElementById('registerPassword').value = '';
            document.getElementById('registerGender').value = '';
            document.getElementById('registerDob').value = '';
            document.getElementById('registerBio').value = '';
            
            // Store user data in localStorage
            localStorage.setItem('user', JSON.stringify({
                id: result.user_id,
                username: username,
                email: email
            }));
            
            // Refresh profiles to show new user
            loadProfilesFromAPI();
        } else {
            alert(result.error || 'Registration failed');
        }
    } catch (error) {
        alert('Registration failed: ' + error.message);
    }
}

// Load profiles from API
async function loadProfilesFromAPI() {
    try {
        const result = await apiCall('profiles');
        
        if (result.success) {
            // Transform API data to match our display format
            const transformedProfiles = result.profiles.map(user => ({
                id: user.id,
                username: user.username,
                age: user.age,
                gender: user.gender,
                location: user.location || 'Location not set',
                bio: user.bio || 'No bio available',
                profile_pic: user.profile_pic || 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=300&h=400&fit=crop&crop=face'
            }));
            
            displayProfiles(transformedProfiles);
        } else {
            console.error('Failed to load profiles:', result.error);
            // Fallback to sample data
            displayProfiles(sampleUsers);
        }
    } catch (error) {
        console.error('Error loading profiles:', error);
        // Fallback to sample data
        displayProfiles(sampleUsers);
    }
}

// Search functionality
async function searchProfiles() {
    const minAge = parseInt(document.getElementById('min-age').value) || 18;
    const maxAge = parseInt(document.getElementById('max-age').value) || 100;
    const gender = document.getElementById('gender').value;
    const location = document.getElementById('location').value.toLowerCase();
    
    // Validate age range
    if (minAge > maxAge) {
        alert('Minimum age cannot be greater than maximum age');
        return;
    }
    
    try {
        const result = await apiCall('search', 'POST', {
            min_age: minAge,
            max_age: maxAge,
            gender: gender,
            location: location
        });
        
        if (result.success) {
            // Transform API data to match our display format
            const transformedProfiles = result.profiles.map(user => ({
                id: user.id,
                username: user.username,
                age: user.age,
                gender: user.gender,
                location: user.location || 'Location not set',
                bio: user.bio || 'No bio available',
                profile_pic: user.profile_pic || 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=300&h=400&fit=crop&crop=face'
            }));
            
            displayProfiles(transformedProfiles);
            
            // Show search results message
            if (result.count === 0) {
                alert('No profiles found matching your criteria. Try adjusting your search filters.');
            } else {
                console.log(`Found ${result.count} profiles matching your search`);
            }
        } else {
            alert(result.error || 'Search failed');
        }
    } catch (error) {
        alert('Search failed: ' + error.message);
        // Fallback to client-side search
        let filteredUsers = sampleUsers.filter(user => {
            const ageMatch = user.age >= minAge && user.age <= maxAge;
            const genderMatch = !gender || user.gender === gender;
            const locationMatch = !location || user.location.toLowerCase().includes(location);
            
            return ageMatch && genderMatch && locationMatch;
        });
        
        displayProfiles(filteredUsers);
    }
}

// Display profiles in the grid
function displayProfiles(users = sampleUsers) {
    const profilesGrid = document.getElementById('profilesGrid');
    
    if (users.length === 0) {
        profilesGrid.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <h3>No profiles found</h3>
                <p>Try adjusting your search criteria.</p>
            </div>
        `;
        return;
    }
    
    profilesGrid.innerHTML = users.map(user => `
        <div class="profile-card-grid">
            <div class="profile-card-image">
                <img src="${user.profile_pic}" alt="${user.username}">
            </div>
            <div class="profile-card-content">
                <div class="profile-card-header">
                    <span class="profile-name">${user.username}</span>
                    <span class="profile-age">${user.age}</span>
                </div>
                <div class="profile-location">
                    <i class="fas fa-map-marker-alt"></i> ${user.location}
                </div>
                <div class="profile-bio">${user.bio}</div>
                <div class="profile-actions">
                    <button class="btn-like" onclick="likeProfile(${user.id})">
                        <i class="fas fa-heart"></i> Like
                    </button>
                    <button class="btn-message" onclick="openMessageModal(${user.id}, '${user.username}')">
                        <i class="fas fa-comment"></i> Message
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Profile interaction functions
async function likeProfile(userId) {
    const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
    
    if (!currentUser.id) {
        alert('Please login to like profiles');
        openModal('loginModal');
        return;
    }
    
    try {
        // In a real app, this would make an API call to save the like
        console.log('Liked profile:', userId);
        
        // Check if it's a mutual match
        const isMatch = Math.random() > 0.7; // 30% chance of match for demo
        
        if (isMatch) {
            alert('🎉 It\'s a match! You and this user liked each other!\n\nYou can now start messaging each other.');
        } else {
            alert('Profile liked! ❤️\n\nThis user will be notified of your interest.');
        }
        
        // Update the like button to show it's been liked
        const likeButton = event.target.closest('.btn-like');
        if (likeButton) {
            likeButton.innerHTML = '<i class="fas fa-heart"></i> Liked';
            likeButton.style.background = '#c2185b';
            likeButton.disabled = true;
        }
        
    } catch (error) {
        alert('Error liking profile: ' + error.message);
    }
}

// Message functionality
function openMessageModal(userId, username) {
    const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
    
    if (!currentUser.id) {
        alert('Please login to send messages');
        openModal('loginModal');
        return;
    }
    
    // Create message modal if it doesn't exist
    if (!document.getElementById('messageModal')) {
        createMessageModal();
    }
    
    // Set the recipient info
    document.getElementById('messageRecipient').textContent = username;
    document.getElementById('messageRecipientId').value = userId;
    
    // Open the modal
    openModal('messageModal');
}

function createMessageModal() {
    const modalHTML = `
        <div id="messageModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('messageModal')">&times;</span>
                <h2>Send Message</h2>
                <form class="message-form" onsubmit="sendMessage(event)">
                    <div class="form-group">
                        <label>To: <span id="messageRecipient"></span></label>
                        <input type="hidden" id="messageRecipientId">
                    </div>
                    <div class="form-group">
                        <label for="messageText">Message:</label>
                        <textarea id="messageText" rows="4" placeholder="Write your message here..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">Send Message</button>
                </form>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

async function sendMessage(event) {
    event.preventDefault();
    
    const recipientId = document.getElementById('messageRecipientId').value;
    const messageText = document.getElementById('messageText').value;
    const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
    
    if (!currentUser.id) {
        alert('Please login to send messages');
        return;
    }
    
    if (!messageText.trim()) {
        alert('Please enter a message');
        return;
    }
    
    try {
        // In a real app, this would make an API call to save the message
        console.log('Sending message:', { recipientId, messageText });
        
        // Simulate sending message
        alert('Message sent successfully! 💬\n\nThe user will be notified of your message.');
        
        // Close modal and clear form
        closeModal('messageModal');
        document.getElementById('messageText').value = '';
        
    } catch (error) {
        alert('Error sending message: ' + error.message);
    }
}

// User dashboard functionality
function showUserDashboard(user) {
    // Create user dashboard if it doesn't exist
    if (!document.getElementById('userDashboard')) {
        createUserDashboard();
    }
    
    // Update user info
    document.getElementById('userName').textContent = user.username;
    document.getElementById('userEmail').textContent = user.email;
    
    // Show dashboard
    document.getElementById('userDashboard').style.display = 'block';
    
    // Hide main content
    document.querySelector('.hero').style.display = 'none';
    document.querySelector('.search-section').style.display = 'none';
    document.querySelector('.profiles-section').style.display = 'none';
    document.querySelector('.success-stories').style.display = 'none';
}

function createUserDashboard() {
    const dashboardHTML = `
        <div id="userDashboard" class="user-dashboard" style="display: none; padding: 120px 0 80px;">
            <div class="container">
                <div class="dashboard-header">
                    <h1>Welcome, <span id="userName"></span>!</h1>
                    <p>Email: <span id="userEmail"></span></p>
                    <button class="btn btn-outline" onclick="logout()">Logout</button>
                </div>
                
                <div class="dashboard-content">
                    <div class="dashboard-section">
                        <h2>Your Matches</h2>
                        <div id="userMatches" class="matches-grid">
                            <p>No matches yet. Start liking profiles to find your matches!</p>
                        </div>
                    </div>
                    
                    <div class="dashboard-section">
                        <h2>Recent Messages</h2>
                        <div id="userMessages" class="messages-list">
                            <p>No messages yet. Start conversations with your matches!</p>
                        </div>
                    </div>
                    
                    <div class="dashboard-section">
                        <h2>Profile Settings</h2>
                        <button class="btn btn-primary" onclick="editProfile()">Edit Profile</button>
                        <button class="btn btn-secondary" onclick="viewProfile()">View Profile</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', dashboardHTML);
}

function logout() {
    localStorage.removeItem('user');
    location.reload();
}

function editProfile() {
    alert('Edit profile functionality will open here');
}

function viewProfile() {
    alert('View profile functionality will open here');
}

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Mobile navigation toggle
document.querySelector('.nav-toggle').addEventListener('click', function() {
    const navMenu = document.querySelector('.nav-menu');
    navMenu.classList.toggle('active');
});

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Try to load profiles from API first, fallback to sample data
    loadProfilesFromAPI();
    
    // Add scroll effect to navbar
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 100) {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.backdropFilter = 'blur(10px)';
        } else {
            navbar.style.background = 'white';
            navbar.style.backdropFilter = 'none';
        }
    });
    
    // Add animation to hero cards
    const heroCards = document.querySelectorAll('.hero-card-1, .hero-card-2, .hero-card-3');
    heroCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.2}s`;
        card.style.animation = 'fadeInUp 0.8s ease forwards';
    });
    
    // Set max date for date of birth (18 years ago)
    const dobInput = document.getElementById('registerDob');
    if (dobInput) {
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        dobInput.max = maxDate.toISOString().split('T')[0];
    }
    
    // Check if user is already logged in
    const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
    if (currentUser.id) {
        showUserDashboard(currentUser);
    }
});

// Add CSS animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .hero-card-1, .hero-card-2, .hero-card-3 {
        opacity: 0;
    }
    
    .user-dashboard {
        background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
        min-height: 100vh;
    }
    
    .dashboard-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .dashboard-header h1 {
        font-size: 2.5rem;
        color: #1a1a1a;
        margin-bottom: 10px;
    }
    
    .dashboard-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
    }
    
    .dashboard-section {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }
    
    .dashboard-section h2 {
        font-size: 1.5rem;
        color: #333;
        margin-bottom: 20px;
    }
    
    .message-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .message-form textarea {
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        font-family: inherit;
        resize: vertical;
        min-height: 100px;
    }
    
    .message-form textarea:focus {
        outline: none;
        border-color: #e91e63;
    }
`;
document.head.appendChild(style); 