# Nexus Notes - Personal Note Keeper

Nexus Notes is a modern, feature-rich web application for managing and organizing your personal notes. It provides a clean, intuitive interface with powerful organization capabilities.

## Features

- **User Authentication**
  - Secure login and signup system
  - Session management
  - Password protection

- **Note Management**
  - Create, edit, and delete notes
  - Rich text editor support
  - Note organization with folders
  - Tag-based categorization
  - Search functionality
  - Export notes as PDF or Markdown

- **Organization**
  - Create and manage folders
  - Tag-based categorization
  - Quick search functionality
  - Sort notes by date

- **Web Clipper**
  - Save content from any webpage directly to your notes
  - Browser bookmarklet for easy access

- **User Interface**
  - Responsive design
  - Dark/Light theme toggle
  - Modern, clean interface
  - Bootstrap-based layout

## Project Structure

```
nexus-notes/
├── api/              # API endpoints
├── assets/           # Static assets (CSS, JS, images)
├── config/           # Configuration files
├── database/         # Database related files
├── includes/         # PHP includes and utilities
├── vendor/           # Composer dependencies
├── bookmarklet.html  # Web clipper bookmarklet
├── clip.php          # Web clipper backend
├── composer.json     # PHP dependencies
├── dashboard.php     # Main dashboard interface
├── index.php         # Landing page
├── login.php         # Login page
├── logout.php        # Logout handler
└── signup.php        # User registration
```

## Installation and Setup

### Prerequisites
- XAMPP (or similar local server environment)
- Composer (PHP package manager)
- Git (for version control)

### Step-by-Step Setup

1. **Install XAMPP**
   - Download and install XAMPP from https://www.apachefriends.org/
   - Make sure to install Apache and MySQL components
   - Start Apache and MySQL services from XAMPP Control Panel

2. **Clone the Project**
   ```bash
   git clone [your-repository-url]
   cd nexus-notes
   ```

3. **Install Dependencies**
   ```bash
   composer install
   ```

4. **Database Setup**
   - Open XAMPP Control Panel
   - Click on "Admin" next to MySQL to open phpMyAdmin
   - Create a new database named `nexus_notes`
   - Import the database schema:
     - In phpMyAdmin, select the `nexus_notes` database
     - Go to "Import" tab
     - Choose the `database/schema.sql` file from your project
     - Click "Go" to import the schema

5. **Configure Database Connection**
   - The default configuration in `config/database.php` is set for XAMPP:
     - Host: localhost
     - Port: 3307
     - Database: nexus_notes
     - Username: root
     - Password: (empty)
   - If you're using different settings, modify the `config/database.php` file accordingly

6. **Run the Application**
   - Place the project folder in your XAMPP's `htdocs` directory (typically `C:\xampp\htdocs\nexus-notes`)
   - Start Apache and MySQL from XAMPP Control Panel
   - Open your web browser and navigate to:
     ```
     http://localhost/nexus-notes
     ```

7. **Initial Setup**
   - You should see the Nexus Notes landing page
   - Click "Sign Up" to create your first account
   - After signing up, you'll be redirected to the dashboard
   - You can now start creating and managing your notes

### Troubleshooting

1. **Database Connection Error**
   - Check if MySQL is running in XAMPP
   - Verify the database name and credentials in `config/database.php`
   - Make sure the database exists and the schema is imported

2. **PHP Error**
   - Check if PHP version is 7.4 or higher
   - Verify that required PHP extensions are enabled (PDO, MySQL)
   - Check the PHP error logs in XAMPP

3. **Web Clipper Issues**
   - Make sure you're logged in to the application
   - Check if the bookmarklet is properly installed in your browser

## Usage

1. **Creating Notes**
   - Click the "New Note" button
   - Enter a title and content
   - Add tags and select a folder
   - Save your note

2. **Organizing Notes**
   - Create folders to categorize notes
   - Add tags to notes for better organization
   - Use the search function to find notes quickly

3. **Web Clipper**
   - Drag the bookmarklet to your browser's bookmarks bar
   - Click it while browsing to save content to your notes

4. **Exporting Notes**
   - Open any note
   - Click the export button
   - Choose between PDF or Markdown format

## Technologies Used

- PHP 7.4+
- MySQL
- Bootstrap 5
- Quill.js (Rich Text Editor)
- Font Awesome
- Composer (Dependency Management)

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue in the GitHub repository or contact the development team. 