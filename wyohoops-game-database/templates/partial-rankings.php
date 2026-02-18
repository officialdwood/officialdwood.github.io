<?php
/**
 * Rankings Tab Partial
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wyohoops-rankings-container">
    <div class="wyohoops-rankings-header">
        <h2>Team Rankings</h2>
        <p class="description">Rankings based on win/loss record, with ratings as tiebreakers</p>
    </div>
    
    <div class="wyohoops-rankings-filters">
        <div class="wyohoops-filter-group">
            <label for="rankings-classification">Classification:</label>
            <select id="rankings-classification" class="wyohoops-filter-select">
                <option value="">All Classifications</option>
                <option value="4A">4A</option>
                <option value="3A">3A</option>
                <option value="2A">2A</option>
                <option value="1A">1A</option>
            </select>
        </div>
        
        <div class="wyohoops-filter-group">
            <label for="rankings-gender">Gender:</label>
            <select id="rankings-gender" class="wyohoops-filter-select">
                <option value="B">Boys</option>
                <option value="G">Girls</option>
            </select>
        </div>
    </div>
    
    <div class="wyohoops-rankings-list" id="rankings-list">
        <!-- Rankings will be loaded via JavaScript -->
        <div class="wyohoops-loading-placeholder">
            <p>Loading rankings...</p>
        </div>
    </div>
</div>

<style>
.wyohoops-ranking-item {
    display: flex;
    align-items: center;
    padding: 20px;
    margin-bottom: 10px;
    background: #1a1a1a;
    border: 1px solid #C8A100;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.wyohoops-ranking-item:hover {
    background: #222;
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(200, 161, 0, 0.3);
}

.wyohoops-ranking-number {
    font-size: 32px;
    font-weight: bold;
    color: #C8A100;
    width: 60px;
    text-align: center;
}

.wyohoops-ranking-team {
    flex: 1;
    margin: 0 20px;
}

.wyohoops-ranking-team-name {
    font-size: 20px;
    font-weight: bold;
    color: #fff;
    margin-bottom: 5px;
}

.wyohoops-ranking-team-info {
    color: #999;
    font-size: 14px;
}

.wyohoops-ranking-record {
    font-size: 24px;
    font-weight: bold;
    color: #C8A100;
    margin: 0 20px;
}

.wyohoops-ranking-ratings {
    display: flex;
    gap: 15px;
}

.wyohoops-ranking-rating {
    text-align: center;
}

.wyohoops-ranking-rating-label {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
}

.wyohoops-ranking-rating-value {
    font-size: 18px;
    font-weight: bold;
    color: #fff;
}
</style>
