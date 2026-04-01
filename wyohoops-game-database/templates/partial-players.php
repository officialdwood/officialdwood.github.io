<?php
/**
 * Player Profile Tab Partial
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wyohoops-players-container">
    <div class="wyohoops-players-header">
        <h2>Player Profiles</h2>
        <p class="description">Top rated players across Wyoming high school basketball</p>
    </div>
    
    <div class="wyohoops-players-filters">
        <div class="wyohoops-filter-group">
            <label for="players-sort">Sort By:</label>
            <select id="players-sort" class="wyohoops-filter-select">
                <option value="overall">Overall Rating</option>
                <option value="offensive">Offensive Rating</option>
                <option value="defensive">Defensive Rating</option>
                <option value="efficiency">Efficiency Rating</option>
                <option value="ppg">Points Per Game</option>
            </select>
        </div>
        
        <div class="wyohoops-filter-group">
            <label for="players-classification">Classification:</label>
            <select id="players-classification" class="wyohoops-filter-select">
                <option value="">All Classifications</option>
                <option value="4A">4A</option>
                <option value="3A">3A</option>
                <option value="2A">2A</option>
                <option value="1A">1A</option>
            </select>
        </div>
        
        <div class="wyohoops-filter-group">
            <label for="players-position">Position:</label>
            <select id="players-position" class="wyohoops-filter-select">
                <option value="">All Positions</option>
                <option value="PG">Point Guard</option>
                <option value="SG">Shooting Guard</option>
                <option value="SF">Small Forward</option>
                <option value="PF">Power Forward</option>
                <option value="C">Center</option>
            </select>
        </div>
    </div>
    
    <div class="wyohoops-players-grid" id="players-grid">
        <!-- Player cards will be loaded via JavaScript -->
        <div class="wyohoops-loading-placeholder">
            <p>Loading player profiles...</p>
        </div>
    </div>
</div>

<style>
.wyohoops-players-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.wyohoops-player-card {
    background: #1a1a1a;
    border: 2px solid #C8A100;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.wyohoops-player-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(200, 161, 0, 0.4);
    border-color: #FFD700;
}

.wyohoops-player-photo {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 15px;
    background: #111;
}

.wyohoops-player-name {
    font-size: 20px;
    font-weight: bold;
    color: #fff;
    margin-bottom: 5px;
}

.wyohoops-player-meta {
    color: #999;
    font-size: 14px;
    margin-bottom: 15px;
}

.wyohoops-player-team {
    color: #C8A100;
    font-weight: 600;
}

.wyohoops-player-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 15px;
}

.wyohoops-player-stat {
    text-align: center;
    padding: 10px;
    background: #111;
    border-radius: 6px;
}

.wyohoops-player-stat-label {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.wyohoops-player-stat-value {
    font-size: 20px;
    font-weight: bold;
    color: #C8A100;
}

.wyohoops-player-ratings {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #333;
}

.wyohoops-player-rating {
    text-align: center;
}

.wyohoops-player-rating-label {
    font-size: 10px;
    color: #999;
    text-transform: uppercase;
}

.wyohoops-player-rating-value {
    font-size: 16px;
    font-weight: bold;
    color: #fff;
}

/* Player Detail Modal */
.wyohoops-player-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.wyohoops-player-modal.active {
    display: flex;
}

.wyohoops-player-modal-content {
    background: #1a1a1a;
    border: 2px solid #C8A100;
    border-radius: 12px;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
}

.wyohoops-player-modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #C8A100;
    color: #000;
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
    transition: all 0.3s ease;
}

.wyohoops-player-modal-close:hover {
    background: #FFD700;
    transform: rotate(90deg);
}
</style>
