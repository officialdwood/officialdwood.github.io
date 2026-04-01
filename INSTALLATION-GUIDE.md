# WyoHoops Game Database - Quick Installation Guide

## Installation Steps

1. **Download the Plugin**
   - Download `wyohoops-game-database.zip` from the repository

2. **Install in WordPress**
   - Log in to your WordPress admin dashboard
   - Navigate to **Plugins > Add New**
   - Click **Upload Plugin** button at the top
   - Click **Choose File** and select `wyohoops-game-database.zip`
   - Click **Install Now**
   - After installation completes, click **Activate Plugin**

3. **Initial Setup**
   - Go to **WyoHoops DB** in the admin menu (look for the trophy icon)
   - Click on **Import/Tools** submenu
   - Click **Import Default Teams** button
   - Wait for success message (imports 69 Wyoming high school teams)

4. **Add the Shortcode to a Page**
   - Create a new page or edit an existing one
   - Add the shortcode: `[wyohoops_gamedb]`
   - Publish the page
   - View your page to see the interactive game database!

## Adding Your First Game

1. Go to **WyoHoops DB > Games**
2. Fill out the form:
   - **Game Date**: Select the date
   - **Gender**: Choose Boys or Girls
   - **Level**: Choose Varsity, JV, or Freshman
   - **Home Team**: Select from the dropdown
   - **Away Team**: Select from the dropdown
   - **Home Score & Away Score**: Leave blank for future games, or enter scores for completed games
3. Click **Add Game**

## Configuring Settings

Go to **WyoHoops DB > Settings** to adjust:
- Efficiency score calculation baselines
- Default gender view (Boys/Girls)
- UI preferences (table vs cards, show/hide meters)
- Performance caching

## Understanding Efficiency Scores

### Offensive Efficiency (0-100)
- Measures how well a team scores points
- Default: Averaging 80 points per game = 98 efficiency
- Higher scores are better
- Shown with a gold meter bar

### Defensive Efficiency (0-100)
- Measures how well a team prevents opponents from scoring
- Default: Holding opponents to 40 points = 96 efficiency
- Higher scores are better
- Shown with a gold meter bar

## Front-End Features

The `[wyohoops_gamedb]` shortcode displays three tabs:

1. **Teams Tab**
   - View all teams with rankings and stats
   - Filter by classification and gender
   - Sort by various metrics
   - Click any team card to see detailed stats and recent games

2. **Schedule Tab**
   - View all games (past and upcoming)
   - Filter by gender, classification, and status
   - See completed scores or scheduled game times

3. **Compare Tab**
   - Select two teams to compare
   - View side-by-side statistics
   - See which team has the advantage in each category

## Troubleshooting

### Plugin won't activate
- Check that you're using WordPress 5.0+ and PHP 7.2+
- Make sure the zip file is not corrupted

### Teams or games not showing
- Make sure you've imported the default teams from Import/Tools
- Clear the statistics cache from Import/Tools page
- Check that the shortcode is correctly placed: `[wyohoops_gamedb]`

### Styling looks broken
- Clear your browser cache
- Check that your theme doesn't have conflicting CSS

## Support

For issues or questions, contact the plugin developer at:
https://officialdwood.github.io

## Next Steps

1. Add more games to build your database
2. Customize team colors and upload logos
3. Adjust efficiency calculation settings to your preference
4. Share your game database page with your community!

Enjoy using WyoHoops Game Database! 🏀
