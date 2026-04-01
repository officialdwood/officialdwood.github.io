<?php
/**
 * Stats Tab Partial
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wyohoops-stats-container">
    <div class="wyohoops-stats-header">
        <h2>Team Statistics</h2>
        <p class="description">Comprehensive team metrics calculated from game data</p>
    </div>
    
    <div class="wyohoops-stats-filters">
        <div class="wyohoops-filter-group">
            <label for="stats-classification">Classification:</label>
            <select id="stats-classification" class="wyohoops-filter-select">
                <option value="">All Classifications</option>
                <option value="4A">4A</option>
                <option value="3A">3A</option>
                <option value="2A">2A</option>
                <option value="1A">1A</option>
            </select>
        </div>
        
        <div class="wyohoops-filter-group">
            <label for="stats-gender">Gender:</label>
            <select id="stats-gender" class="wyohoops-filter-select">
                <option value="B">Boys</option>
                <option value="G">Girls</option>
            </select>
        </div>
        
        <div class="wyohoops-filter-group">
            <label for="stats-metric">Primary Metric:</label>
            <select id="stats-metric" class="wyohoops-filter-select">
                <option value="win_pct">Win Percentage</option>
                <option value="offensive_eff">Offensive Efficiency</option>
                <option value="defensive_eff">Defensive Efficiency</option>
                <option value="ppg">Points Per Game</option>
                <option value="papg">Points Allowed Per Game</option>
                <option value="point_diff">Point Differential</option>
            </select>
        </div>
    </div>
    
    <div class="wyohoops-stats-table-container" id="stats-table-container">
        <!-- Stats table will be loaded via JavaScript -->
        <div class="wyohoops-loading-placeholder">
            <p>Loading team statistics...</p>
        </div>
    </div>
</div>

<style>
.wyohoops-stats-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: #1a1a1a;
    border-radius: 8px;
    overflow: hidden;
}

.wyohoops-stats-table thead {
    background: #C8A100;
    color: #000;
}

.wyohoops-stats-table th {
    padding: 15px 10px;
    text-align: left;
    font-weight: bold;
    font-size: 12px;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.3s ease;
}

.wyohoops-stats-table th:hover {
    background: #FFD700;
}

.wyohoops-stats-table th.sortable::after {
    content: '⇅';
    margin-left: 5px;
    opacity: 0.5;
}

.wyohoops-stats-table th.sorted-asc::after {
    content: '▲';
    opacity: 1;
}

.wyohoops-stats-table th.sorted-desc::after {
    content: '▼';
    opacity: 1;
}

.wyohoops-stats-table tbody tr {
    border-bottom: 1px solid #333;
    transition: background 0.3s ease;
}

.wyohoops-stats-table tbody tr:hover {
    background: #222;
}

.wyohoops-stats-table td {
    padding: 12px 10px;
    color: #fff;
    font-size: 14px;
}

.wyohoops-stats-team-cell {
    font-weight: bold;
}

.wyohoops-stats-team-name {
    color: #C8A100;
}

.wyohoops-stats-team-classification {
    color: #999;
    font-size: 12px;
    font-weight: normal;
}

.wyohoops-stats-value-high {
    color: #4CAF50;
    font-weight: bold;
}

.wyohoops-stats-value-low {
    color: #f44336;
}

.wyohoops-stats-value-medium {
    color: #FFC107;
}

/* Responsive */
@media (max-width: 768px) {
    .wyohoops-stats-table {
        font-size: 12px;
    }
    
    .wyohoops-stats-table th,
    .wyohoops-stats-table td {
        padding: 8px 5px;
    }
    
    .wyohoops-stats-table th {
        font-size: 10px;
    }
}

/* Stats Summary Cards */
.wyohoops-stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.wyohoops-stats-summary-card {
    background: #1a1a1a;
    border: 1px solid #C8A100;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.wyohoops-stats-summary-label {
    font-size: 12px;
    color: #999;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.wyohoops-stats-summary-value {
    font-size: 32px;
    font-weight: bold;
    color: #C8A100;
}

.wyohoops-stats-summary-sublabel {
    font-size: 11px;
    color: #666;
    margin-top: 5px;
}
</style>
