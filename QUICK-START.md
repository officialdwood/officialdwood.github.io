# WyoHoops Database - Quick Start Guide

## What's Been Built

A complete WordPress plugin containing **all 69 Wyoming high school basketball teams** from the 2025-2026 season, organized by classification (4A, 3A, 2A, 1A).

## Installation

### Option 1: Upload via WordPress Admin (Recommended)
1. Go to WordPress Admin → **Plugins** → **Add New**
2. Click **Upload Plugin**
3. Choose `/wyohoops-game-database.zip`
4. Click **Install Now** → **Activate**

### Option 2: Manual Installation
1. Extract `wyohoops-game-database.zip`
2. Upload the `wyohoops-game-database` folder to `/wp-content/plugins/`
3. Activate the plugin in WordPress Admin

## Loading the School Database

After activation:

1. Go to **WyoHoops DB** (in admin menu)
2. Click **Import/Tools** submenu
3. Click the **"Import Default Teams"** button
4. Wait for success message: "Successfully imported 69 teams!"

This loads all Wyoming schools into your database:
- 15 schools from 4A classification
- 16 schools from 3A classification
- 14 schools from 2A classification
- 24 schools from 1A classification

## Using the Database

### Display on Your Website
Add this shortcode to any page or post:
```
[wyohoops_gamedb]
```

This creates an interactive interface with three tabs:
- **Teams Tab**: Browse all teams with rankings and stats
- **Schedule Tab**: View game schedules
- **Compare Tab**: Compare two teams side-by-side

### Managing Teams
Go to **WyoHoops DB** → **Teams** to:
- View all imported schools
- Edit team information
- Add logos and colors
- Mark teams as active/inactive

### Adding Games
Go to **WyoHoops DB** → **Games** to:
- Schedule new games
- Enter scores for completed games
- Track home/away matchups
- Mark conference and postseason games

## Team Data Included

Each of the 69 Wyoming schools includes:

| Field | Description | Example |
|-------|-------------|---------|
| **Name** | Full school name | "Campbell County" |
| **Abbreviation** | Short code | "CC" |
| **Classification** | Level | "4A" |
| **Location** | City | "Gillette" |
| **Colors** | Primary/Secondary | Gold & Black (customizable) |

## Features Available

Once schools are imported, you can:
- ✅ Track game scores and schedules
- ✅ Calculate offensive efficiency (0-100 scale)
- ✅ Calculate defensive efficiency (0-100 scale)
- ✅ Generate automatic team rankings
- ✅ Filter by classification (4A/3A/2A/1A)
- ✅ Filter by gender (Boys/Girls)
- ✅ Compare teams head-to-head
- ✅ View win/loss records
- ✅ Track points for/against averages

## Verification

To verify all schools are loaded:
1. After importing, go to **WyoHoops DB** → **Teams**
2. You should see all 69 teams listed
3. Use the classification filter to view each level:
   - Select "4A" - should show 15 teams
   - Select "3A" - should show 16 teams
   - Select "2A" - should show 14 teams
   - Select "1A" - should show 24 teams

## Next Steps

1. **Install the plugin** (see Installation above)
2. **Import default teams** (WyoHoops DB → Import/Tools)
3. **Add the shortcode** to a page: `[wyohoops_gamedb]`
4. **Start adding games** (WyoHoops DB → Games)
5. **Customize team colors and logos** (WyoHoops DB → Teams)

## Support Files

- `README.md` - Complete plugin documentation
- `INSTALLATION-GUIDE.md` - Detailed installation instructions
- `PROJECT-SUMMARY.md` - Technical specifications
- `WYOMING-SCHOOLS-VERIFICATION.md` - School database verification

## Database Location

All 69 Wyoming schools are coded in:
```
/wyohoops-game-database/includes/class-admin.php
Lines 367-446: get_default_teams_data() method
```

## Questions?

Refer to the comprehensive documentation in `README.md` or `INSTALLATION-GUIDE.md` for detailed instructions on using all features of the WyoHoops Game Database plugin.

---

**Status**: ✅ Database Complete - All 69 Wyoming Schools Included
**Version**: 1.0.0
**Last Updated**: February 9, 2026
