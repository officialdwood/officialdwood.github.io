# WyoHoops Database - Quick Reference Guide

## What Was Built

A complete WordPress plugin for Wyoming high school basketball with:

### ✅ Frontend (User-Facing)
- **Logo Header** - Customizable WyoHoops branding
- **4 Interactive Tabs**:
  1. Teams - All schools with records and ratings
  2. Rankings - Live team rankings by record
  3. Player Profile - Top players with stats
  4. Stats - Team metrics dashboard

### ✅ Backend (Admin Interface)
- **Teams Management** - Add/edit teams, colors, ratings
- **Games Management** - Track games and scores
- **Players Management** - Roster and player profiles
- **Settings** - Upload logo, configure efficiency baselines
- **Import/Tools** - Bulk data import for teams and games

### ✅ Design
- **Color Scheme**: White, Light Gray, Black, Metallic Gold
- **Animations**: Smooth 0.3s transitions throughout
- **Responsive**: Works on all screen sizes
- **Premium**: Metallic gold frame and accents

---

## Installation

1. **Upload**: Go to Plugins → Add New → Upload Plugin
2. **Select**: Choose `wyohoops-game-database.zip`
3. **Activate**: Click Activate Plugin

## Setup

1. **Import Teams**: WyoHoops DB → Import/Tools → "Import Default Teams"
2. **Import Records**: Click "Import Basketball Records"
3. **Upload Logo**: WyoHoops DB → Settings → Upload WyoHoops Logo
4. **Add to Page**: Add shortcode `[wyohoops_gamedb]` to any page

---

## Shortcode

```
[wyohoops_gamedb]
```

Optional parameters:
- `default_tab="teams"` - Start on Teams tab
- `default_tab="rankings"` - Start on Rankings tab
- `default_tab="players"` - Start on Player Profile tab
- `default_tab="stats"` - Start on Stats tab

---

## Admin Menu Structure

**WyoHoops DB** (main menu)
- Teams - Manage all 69 Wyoming schools
- Games - Add/edit games and scores
- Players - Manage rosters and player profiles
- Settings - Logo, efficiency baselines, UI options
- Import/Tools - Bulk data import

---

## Key Features

### Teams
- 69 Wyoming schools (4A, 3A, 2A, 1A)
- Team colors and mascots
- Offensive/Defensive/Overall ratings (adjustable)
- Win/loss records
- Active/inactive status

### Games
- Home/away matchups
- Scores and dates
- Boys/Girls, Varsity/JV/Freshman
- Conference and postseason tracking
- Auto-calculates team statistics

### Players
- Full roster management
- Player photos
- Height, weight, position
- Offensive/Defensive/Overall/Efficiency ratings
- PPG, RPG, APG, steals, blocks
- Shooting percentages
- Biography
- Profile visibility toggle

### Rankings
- Auto-sorted by win/loss record
- Uses ratings as tiebreakers
- Live updates from game data
- Filter by classification and gender

### Statistics
- Win percentage
- Offensive efficiency (0-100)
- Defensive efficiency (0-100)
- Points per game
- Points allowed per game
- Point differential

---

## Data Included

### Teams (69 total)
- **4A**: 15 schools (Sheridan, Campbell County, etc.)
- **3A**: 16 schools (Lovell, Cody, Powell, etc.)
- **2A**: 14 schools (Wyoming Indian, Thermopolis, etc.)
- **1A**: 24 schools (Lingle-Fort Laramie, Saratoga, etc.)

### Game Records
- 2025-2026 season data
- Boys Varsity records
- Win/loss data for all teams

---

## Customization

### Logo
1. Go to WyoHoops DB → Settings
2. Click "Upload Logo"
3. Select image from Media Library
4. Save Settings

### Team Ratings
1. Go to WyoHoops DB → Teams
2. Click Edit on any team
3. Adjust Offensive/Defensive/Overall ratings (0-100)
4. Save Team

### Player Profiles
1. Go to WyoHoops DB → Players
2. Click "Add New Player"
3. Fill in all details
4. Check "Player has a public profile"
5. Save Player

---

## Technical Details

### Database Tables
- `wp_wyohoops_teams` - Team information
- `wp_wyohoops_games` - Game tracking
- `wp_wyohoops_players` - Player profiles

### REST API Endpoints
- `/wyohoops/v1/teams` - Get teams
- `/wyohoops/v1/rankings` - Get rankings
- `/wyohoops/v1/players` - Get player profiles
- `/wyohoops/v1/stats` - Get team statistics
- `/wyohoops/v1/games` - Get games
- `/wyohoops/v1/compare` - Compare teams

### Security
- Nonces on all forms
- Capability checks (manage_options)
- Prepared SQL statements
- Input sanitization
- Output escaping

---

## Support

### Requirements
- WordPress 5.0+
- PHP 7.2+
- MySQL 5.6+

### Plugin Info
- **Name**: WyoHoops Game Database
- **Version**: 1.1.0
- **Size**: 56KB
- **Files**: 33 total (PHP, CSS, JS, templates)

---

## What's Next

After installation, you can:

1. **Customize Teams** - Edit colors, logos, ratings
2. **Add Games** - Track more games and scores
3. **Add Players** - Build rosters and profiles
4. **Adjust Settings** - Fine-tune efficiency calculations
5. **Brand It** - Upload your custom logo

The plugin is fully functional out of the box with Wyoming schools and records pre-loaded!

---

## Quick Checklist

- [ ] Install plugin
- [ ] Activate plugin
- [ ] Import default teams (69 schools)
- [ ] Import basketball records
- [ ] Upload WyoHoops logo
- [ ] Add shortcode to page
- [ ] Publish page
- [ ] View frontend display
- [ ] Customize as needed

---

**File**: `wyohoops-game-database.zip` (56KB)
**Status**: Production Ready ✅
**Last Updated**: February 18, 2026
