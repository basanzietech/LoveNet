# LoveNet - Modern Dating Website

A modern, clean, and responsive dating website built with HTML, CSS, and JavaScript. Features a beautiful user interface with search functionality, user profiles, success stories, and a comprehensive admin dashboard.

## 🌟 Features

### User Interface
- **Modern Design**: Clean, professional design with light theme colors (white, soft pinks, subtle blues)
- **Responsive Layout**: Mobile-friendly design that works on all devices
- **Hero Section**: Welcoming banner with attractive profile cards
- **Search Filters**: Age, gender, and location-based search functionality
- **Profile Cards**: Beautiful user profile previews with photos and basic info
- **Success Stories**: Testimonials from successful couples
- **Authentication**: Login and registration modals

### Admin Dashboard
- **Analytics Overview**: User statistics and growth metrics
- **User Management**: View, edit, and ban users
- **Report System**: Handle user reports and complaints
- **Message Monitoring**: View and manage user messages
- **Profile Verifications**: Approve or reject user verifications
- **System Settings**: Configure website settings and security

## 🗄️ Database Schema

### TBL_USERS
| Column         | Type         | Description                  |
|----------------|--------------|------------------------------|
| id             | INT, PK      | Unique ID                   |
| username       | VARCHAR      | User's display name         |
| email          | VARCHAR      | Unique email                |
| phone          | VARCHAR      | Optional                    |
| password       | VARCHAR      | Hashed password             |
| gender         | ENUM         | 'male', 'female', 'other'   |
| dob            | DATE         | Date of birth               |
| bio            | TEXT         | About user                  |
| profile_pic    | VARCHAR      | Image path                  |
| status         | ENUM         | 'active', 'banned'          |
| created_at     | TIMESTAMP    | Registration time           |

### TBL_ADMINS
| Column      | Type      | Description                |
|-------------|-----------|----------------------------|
| id          | INT, PK   | Unique ID                 |
| username    | VARCHAR   | Admin name                |
| email       | VARCHAR   | Email                     |
| password    | VARCHAR   | Hashed password           |
| role        | ENUM      | 'super_admin', 'moderator'|
| status      | ENUM      | 'active', 'disabled'      |

### TBL_MESSAGES
| Column     | Type       | Description                   |
|------------|------------|-------------------------------|
| id         | INT, PK    | Unique message ID             |
| sender_id  | INT        | From user                     |
| receiver_id| INT        | To user                       |
| message    | TEXT       | Message body                  |
| sent_at    | TIMESTAMP  | Time sent                     |

### TBL_REPORTS
| Column     | Type      | Description                     |
|------------|-----------|---------------------------------|
| id         | INT, PK   | Unique report ID               |
| reported_id| INT       | ID of reported user             |
| reason     | TEXT      | Report reason                   |
| reporter_id| INT       | ID of user who reported         |
| status     | ENUM      | 'pending', 'reviewed'          |

### TBL_VERIFICATIONS
| Column       | Type     | Description                     |
|--------------|----------|---------------------------------|
| id           | INT, PK  | Unique ID                       |
| user_id      | INT      | Reference to TBL_USERS          |
| selfie       | VARCHAR  | Selfie image path               |
| status       | ENUM     | 'pending', 'verified', 'rejected'|

## 🚀 Getting Started

### Prerequisites
- A modern web browser
- Basic knowledge of HTML, CSS, and JavaScript

### Installation

1. **Clone or download the project**
   ```bash
   git clone <repository-url>
   cd LoveNet
   ```

2. **Open the website**
   - Open `index.html` in your web browser
   - Or serve the files using a local server:
     ```bash
     # Using Python
     python -m http.server 8000
     
     # Using Node.js (if you have http-server installed)
     npx http-server
     
     # Using PHP
     php -S localhost:8000
     ```

3. **Access the admin dashboard**
   - Open `admin-dashboard.html` in your browser
   - Or navigate to it from the main page

## 📁 Project Structure

```
LoveNet/
├── index.html              # Main homepage
├── admin-dashboard.html    # Admin dashboard
├── styles.css              # Main website styles
├── admin-styles.css        # Admin dashboard styles
├── script.js               # Main website JavaScript
├── admin-script.js         # Admin dashboard JavaScript
└── README.md               # Project documentation
```

## 🎨 Design Features

### Color Scheme
- **Primary**: Soft pink (#e91e63)
- **Secondary**: Light blue (#e3f2fd)
- **Background**: Off-white (#fafafa)
- **Text**: Dark gray (#333)
- **Accents**: Subtle blues and pinks

### Typography
- **Font Family**: Inter (Google Fonts)
- **Weights**: 300, 400, 500, 600, 700
- **Responsive**: Scales appropriately on all devices

### Components
- **Cards**: Rounded corners with subtle shadows
- **Buttons**: Gradient backgrounds with hover effects
- **Modals**: Backdrop blur with smooth animations
- **Tables**: Clean, organized data presentation
- **Charts**: Interactive data visualization

## 🔧 Customization

### Adding New Features
1. **User Profiles**: Extend the profile cards with additional information
2. **Messaging System**: Implement real-time chat functionality
3. **Matching Algorithm**: Add sophisticated matching logic
4. **Payment Integration**: Add premium features and subscriptions
5. **Mobile App**: Create native mobile applications

### Styling Changes
- Modify `styles.css` for main website changes
- Modify `admin-styles.css` for admin dashboard changes
- Update color variables for consistent theming

### Database Integration
- Replace sample data with real database queries
- Implement user authentication and session management
- Add API endpoints for data operations
- Implement real-time updates using WebSockets

## 📱 Responsive Design

The website is fully responsive and works on:
- **Desktop**: Full-featured experience
- **Tablet**: Optimized layout for medium screens
- **Mobile**: Touch-friendly interface with collapsible navigation

## 🔒 Security Considerations

### For Production Use
- Implement proper user authentication
- Add CSRF protection
- Use HTTPS for all communications
- Implement rate limiting
- Add input validation and sanitization
- Use prepared statements for database queries
- Implement proper session management

## 🚀 Deployment

### Static Hosting
- Upload files to services like Netlify, Vercel, or GitHub Pages
- Configure custom domain if needed

### Server Hosting
- Set up a web server (Apache, Nginx)
- Configure PHP/Node.js backend if needed
- Set up database (MySQL, PostgreSQL)
- Configure SSL certificates

## 🤝 Contributing

1. Fork the project
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 📞 Support

For support or questions:
- Create an issue in the repository
- Contact the development team
- Check the documentation

## 🎯 Roadmap

### Phase 1 (Current)
- ✅ Basic user interface
- ✅ Admin dashboard
- ✅ Responsive design
- ✅ Sample data integration

### Phase 2 (Future)
- 🔄 Backend API development
- 🔄 Database integration
- 🔄 User authentication
- 🔄 Real-time messaging

### Phase 3 (Future)
- 🔄 Advanced matching algorithm
- 🔄 Mobile app development
- 🔄 Payment integration
- 🔄 Advanced analytics

---

**Built with ❤️ for connecting hearts around the world** 