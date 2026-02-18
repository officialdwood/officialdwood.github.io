# Wyoming Basketball Records Import Guide

## Overview

The WyoHoops Game Database plugin now includes functionality to import actual basketball records from the "WY Basketball Records.pdf" file.

## What's Included

### PDF Data Source
- **File**: `WY Basketball Records.pdf` (4.0 MB)
- **Season**: 2025-2026 Boys Varsity Basketball
- **Date**: February 17, 2026
- **Teams**: 69 Wyoming high school teams across all classifications

### Team Records Extracted

#### 4A Boys Basketball (15 teams)

**Region East:**
- Sheridan: 14-1
- Cheyenne Central: 13-6
- Thunder Basin: 11-8
- Cheyenne East: 10-10
- Campbell County: 8-10
- Laramie: 7-12
- Cheyenne South: 1-18

**Region West:**
- Green River: 14-4
- Rock Springs: 14-4
- Natrona County: 13-5
- Star Valley: 9-7
- Kelly Walsh: 7-10
- Riverton: 7-13
- Evanston: 5-13
- Jackson Hole: 1-14

#### 3A Boys Basketball (16 teams)

**Region East:**
- Douglas: 16-4
- Buffalo: 12-6
- Wheatland: 9-10
- Rawlins: 7-11
- Newcastle: 6-13
- Burns: 6-16
- Torrington: 5-10
- Glenrock: 4-14

**Region West:**
- Lovell: 17-2 (Best record in 3A)
- Powell: 13-5
- Lander Valley: 11-7
- Pinedale: 11-7
- Lyman: 9-7
- Mountain View: 8-9
- Worland: 8-11
- Cody: 6-12

#### 2A Boys Basketball (14 teams)

**Region East:**
- Big Horn: 16-4
- Wright: 15-5
- Pine Bluffs: 14-6
- Sundance: 6-14
- Moorcroft: 5-16
- Tongue River: 1-19

**Region West:**
- Wyoming Indian: 20-2 (Best overall record)
- Thermopolis: 16-4
- Shoshoni: 11-9
- Rocky Mountain: 10-11
- Greybull: 9-14
- Big Piney: 7-10
- Kemmerer: 4-13
- Wind River: 1-20

#### 1A Boys Basketball (24 teams)

**Notable Records:**
- Lingle-Fort Laramie: 17-2
- Saratoga: 15-3
- Meeteetse: 14-5
- Upton: 14-5
- Niobrara County: 14-3
- Burlington: 14-7
- Little Snake River: 13-5
- Hulett: 12-3
- And 16 more teams...

## How to Import

### Step 1: Install Plugin
1. Upload `wyohoops-game-database.zip` to WordPress
2. Activate the plugin
3. Go to **WyoHoops DB** in WordPress admin

### Step 2: Import Teams
1. Navigate to **WyoHoops DB** → **Import/Tools**
2. Click **"Import Default Teams"** button
3. Wait for confirmation: "Successfully imported 69 teams!"

### Step 3: Import Basketball Records
1. Still on the **Import/Tools** page
2. Click **"Import Basketball Records"** button
3. Confirm the import when prompted
4. The system will generate games based on team records
5. Wait for confirmation message

## What Happens During Import

### Game Generation
The import process:
1. Reads win-loss records for all 69 teams
2. Generates simulated games matching the actual records
3. Distributes games across the 2025-2026 season (December-February)
4. Creates realistic scores based on win/loss outcomes
5. Assigns appropriate weeks and conference designations

### Data Created
- **Games Generated**: Varies based on records (15-22 games per team)
- **Total Games**: Approximately 600+ games
- **Date Range**: December 2025 - February 2026
- **Game Details**: Date, time, location, scores, week labels

### Statistics Calculated
Once imported, the system automatically calculates:
- Win/Loss records (matches PDF data)
- Win percentages
- Points for/against averages
- Offensive Efficiency (0-100 scale)
- Defensive Efficiency (0-100 scale)
- Team rankings by classification
- Point differentials

## Viewing the Data

### Admin Interface
- **Teams Page**: View all teams with updated records
- **Games Page**: Browse all imported games
- **Statistics**: View calculated stats and rankings

### Front-End Display
Add shortcode to any page:
```
[wyohoops_gamedb]
```

Features three tabs:
1. **Teams Tab**: Rankings, records, efficiency scores
2. **Schedule Tab**: All games (completed with scores)
3. **Compare Tab**: Head-to-head team comparisons

## Data Accuracy

### Source
All records extracted from official "WY Basketball Records.pdf" dated 2/17/26

### Accuracy Notes
- ✅ Win-loss records: 100% accurate (from PDF)
- ✅ Team names: Verified against official roster
- ✅ Classifications: Confirmed from PDF regions
- ⚠️ Individual game scores: Simulated (realistic but not from actual games)
- ⚠️ Specific game dates: Distributed across season (not actual dates)
- ⚠️ Opponents: Paired within classifications (may not match actual schedule)

### Why Simulated Games?
The PDF provides only overall records (wins-losses), not individual game details. The import system generates games that result in the correct final records while maintaining realistic:
- Score distributions
- Home/away balances
- Conference game frequencies
- Season timelines

## Troubleshooting

### Import Fails
If the import doesn't work:
1. Ensure teams are imported first (Import Default Teams)
2. Check that you have "manage_options" capability
3. Clear the statistics cache (Recalculate Stats button)

### Wrong Statistics
If stats don't match expected values:
1. Go to **Import/Tools**
2. Click **"Recalculate All Stats"**
3. Wait for confirmation
4. Refresh the front-end display

### Missing Teams
If some teams don't have records:
1. Verify all 69 teams were imported
2. Check the Teams page for inactive teams
3. Re-import if necessary

## Advanced Usage

### Custom Imports
Developers can extend the import functionality:

```php
// Add custom team records
add_filter('wyohoops_custom_records', function($records) {
    $records['My School'] = array(20, 5); // 20 wins, 5 losses
    return $records;
});
```

### Statistics Tuning
Adjust efficiency calculations in **Settings** page:
- Offensive Efficiency baseline (default: 80 points = 98 efficiency)
- Defensive Efficiency baseline (default: 40 points allowed = 96 efficiency)

## Future Enhancements

Planned features:
- Import actual game schedules with dates
- Add tournament/playoff games
- Import girls basketball records
- Add JV and freshman level data
- Historical season comparisons

## Support

For issues or questions:
1. Check the main README.md
2. Review INSTALLATION-GUIDE.md
3. Verify DATABASE-STATUS.md for system health

## Credits

Data Source: Wyoming High School Activities Association (WHSAA)
PDF: "WY Basketball Records.pdf" 
Season: 2025-2026 Boys Varsity Basketball
Last Updated: February 17, 2026
