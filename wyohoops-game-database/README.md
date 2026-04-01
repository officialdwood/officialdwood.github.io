# WyoHoops Game Database

A state-of-the-art WordPress plugin that provides a Wyoming high school basketball team and game database with advanced statistics, rankings, and comparison tools.

## Features

### Team Management
- Complete database of Wyoming high school basketball teams (4A, 3A, 2A, 1A)
- Team profiles with colors, logos, and school photos
- Active/inactive team status management
- Team classification and location tracking

### Game Tracking
- Comprehensive game scheduling system
- Track past and upcoming games
- Support for multiple levels (Varsity, JV, Freshman)
- Both boys and girls basketball tracking
- Conference and postseason game designation

### Advanced Statistics
- **Offensive Efficiency Score (0-100)**: Calculated based on average points scored per game
  - Default: 80 points per game = 98 efficiency score (configurable)
- **Defensive Efficiency Score (0-100)**: Calculated based on average points allowed per game
  - Default: Holding opponent to 40 points = 96 efficiency score (configurable)
- Win/Loss records and percentages
- Points for/against averages
- Point differential tracking
- Automatic ranking system based on win%, offensive efficiency, defensive efficiency, and point differential

### Front-End Features
Three interactive tabs via the `[wyohoops_gamedb]` shortcode:

1. **Teams Tab**
   - Search teams by name, abbreviation, or city
   - Filter by classification (4A/3A/2A/1A) and gender (Boys/Girls)
   - Sort by ranking, wins, losses, win%, offensive/defensive efficiency, points, and point differential
   - Beautiful card layout with team colors and avatars
   - Visual efficiency meter bars
   - Click any team to view detailed statistics and recent games

2. **Schedule Tab**
   - View all scheduled and completed games
   - Filter by gender, classification, and game status (completed/upcoming)
   - Filter for conference games only or postseason games only
   - Search by team name
   - Color-coded game cards with clear date and matchup information

3. **Compare Tab**
   - Select any two teams to compare head-to-head
   - Side-by-side statistics comparison
   - Automatic edge indicators showing which team has the advantage
   - Matchup preview with offensive vs defensive analysis

### Admin Dashboard
Complete WordPress admin interface with four sections:

1. **Teams**
   - Add/edit/delete teams
   - Upload team logos and school photos
   - Set team colors with color picker
   - Search and filter teams
   - Import default Wyoming teams with one click

2. **Games**
   - Add/edit/delete games
   - Set game date, time, location
   - Enter scores for completed games
   - Mark conference and postseason games
   - Filter and search games

3. **Settings**
   - Configure efficiency score baselines
   - Set default gender view
   - Choose which game levels to include in statistics
   - Toggle UI options (table vs cards, efficiency meters)
   - Enable/disable statistics caching for performance

4. **Import/Tools**
   - Import default Wyoming high school teams (69 teams)
   - **Import WY Basketball Records** - Import 2025-2026 season data from PDF
   - Recalculate all statistics (clear cache)
   - View database information and statistics

### Basketball Records Import
The plugin includes functionality to import actual 2025-2026 Boys Varsity Basketball season records from "WY Basketball Records.pdf":
- All 69 Wyoming teams with win-loss records
- Games generated from actual team records
- Includes all classifications: 4A, 3A, 2A, 1A
- See `BASKETBALL-RECORDS-IMPORT.md` for detailed information

### Design
Premium dark theme with matte black background and metallic gold accents:
- Professional, modern interface
- Fully responsive on all devices
- Smooth transitions and animations
- Optimized for readability

## Installation

1. Download the `wyohoops-game-database.zip` file
2. Log in to your WordPress admin dashboard
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin**
5. Choose the downloaded `.zip` file
6. Click **Install Now**
7. After installation, click **Activate Plugin**

## Quick Start

### Import Default Teams
1. Go to **WyoHoops DB > Import/Tools**
2. Click **Import Default Teams** button
3. Wait for confirmation (imports 69 Wyoming high school teams)

### Add Games
1. Go to **WyoHoops DB > Games**
2. Fill out the game form:
   - Select game date and time
   - Choose home and away teams
   - Select gender (Boys/Girls) and level (Varsity/JV/Freshman)
   - Enter scores if game is completed
   - Save the game
3. Repeat for all games

### Display on Your Site
1. Create or edit a page/post
2. Add the shortcode: `[wyohoops_gamedb]`
3. Publish and view your interactive game database!

## Shortcode Usage

Basic usage:
```
[wyohoops_gamedb]
```

With parameters:
```
[wyohoops_gamedb default_tab="teams" classification="4A" gender="B"]
```

Parameters:
- `default_tab`: Which tab to show first (teams, schedule, or compare)
- `classification`: Pre-filter by classification (4A, 3A, 2A, 1A)
- `gender`: Pre-filter by gender (B for Boys, G for Girls)

## Efficiency Score Calculations

### Offensive Efficiency
By default, offensive efficiency is calculated as:
```
OffEff = min(100, max(0, round((avg_points_for / 80) * 98)))
```

This means:
- A team averaging 80 points per game gets a score of 98
- A team averaging 81.6+ points per game gets the maximum score of 100
- Scores scale proportionally below 80 points

### Defensive Efficiency
By default, defensive efficiency is calculated as:
```
DefEff = min(100, max(0, round((40 / avg_points_against) * 96)))
```

This means:
- A team holding opponents to 40 points per game gets a score of 96
- A team holding opponents to 41.7+ points per game gets the maximum score of 100
- Scores scale proportionally above 40 points

Both baselines can be adjusted in **Settings** to match your preferences.

## Ranking System

Teams are ranked using the following priority:
1. Win Percentage (highest first)
2. Offensive Efficiency (highest first)
3. Defensive Efficiency (highest first)
4. Point Differential (highest first)

## Database Tables

The plugin creates two custom database tables:

1. **wp_wyohoops_teams** - Stores team information
2. **wp_wyohoops_games** - Stores game data

These tables are created automatically on plugin activation using WordPress's `dbDelta` function.

## Performance

The plugin includes an optional caching system that stores calculated statistics in WordPress transients. This significantly improves performance when displaying team rankings and statistics.

- Cache duration: 1 hour
- Automatically cleared when games are added/updated/deleted
- Can be manually cleared from **Import/Tools** page

## Security

The plugin follows WordPress best practices for security:
- All database queries use prepared statements
- Input validation and sanitization on all forms
- Nonce verification for all admin actions and AJAX requests
- Capability checks (requires `manage_options` for admin features)
- XSS protection with proper escaping

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## File Structure

```
wyohoops-game-database/
├── wyohoops-game-database.php    # Main plugin file
├── includes/
│   ├── class-activator.php       # Plugin activation
│   ├── class-deactivator.php     # Plugin deactivation
│   ├── class-plugin.php          # Main plugin class
│   ├── class-admin.php           # Admin functionality
│   ├── class-public.php          # Public shortcode
│   ├── class-repository-teams.php # Team data access
│   ├── class-repository-games.php # Game data access
│   ├── class-stats-service.php   # Statistics calculations
│   └── class-rest-api.php        # REST API endpoints
├── assets/
│   ├── css/
│   │   ├── admin.css            # Admin styles
│   │   └── public.css           # Front-end styles
│   └── js/
│       ├── admin.js             # Admin JavaScript
│       └── public.js            # Front-end JavaScript
├── templates/
│   ├── admin-teams.php          # Teams admin page
│   ├── admin-games.php          # Games admin page
│   ├── admin-settings.php       # Settings page
│   ├── admin-tools.php          # Import/Tools page
│   ├── shortcode-gamedb.php     # Main shortcode template
│   ├── partial-teams.php        # Teams tab
│   ├── partial-schedule.php     # Schedule tab
│   └── partial-compare.php      # Compare tab
└── README.md                     # This file
```

## Support

For issues, questions, or feature requests, please contact the plugin developer.

## License

GPL v2 or later

## Credits

Developed by Official D Wood
https://officialdwood.github.io

## Changelog

### Version 1.0.0
- Initial release
- Team management system
- Game tracking system
- Advanced statistics calculations (offensive/defensive efficiency)
- Automatic ranking system
- Front-end shortcode with three interactive tabs
- Admin dashboard with complete CRUD operations
- Import tool for Wyoming high school teams
- Dark theme with matte black and metallic gold design
- Fully responsive layout
- REST API for AJAX interactions
- Statistics caching system
- Security hardening with nonces and capability checks
