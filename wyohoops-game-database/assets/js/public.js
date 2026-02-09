/**
 * WyoHoops Game Database - Public JavaScript
 */

(function($) {
    'use strict';
    
    var WyoHoopsApp = {
        currentTab: 'teams',
        currentGender: wyohoopsPublic.default_gender,
        currentClassification: '',
        currentSort: 'rank',
        teams: [],
        
        init: function() {
            this.bindEvents();
            this.loadTeamsTab();
        },
        
        bindEvents: function() {
            var self = this;
            
            // Tab switching
            $('.wyohoops-tab-button').on('click', function() {
                var tab = $(this).data('tab');
                self.switchTab(tab);
            });
            
            // Teams tab filters
            $('#wyohoops-search').on('input', function() {
                self.loadTeamsTab();
            });
            
            $('#wyohoops-gender-filter').on('change', function() {
                self.currentGender = $(this).val();
                self.loadTeamsTab();
            });
            
            $('#wyohoops-class-filter').on('change', function() {
                self.currentClassification = $(this).val();
                self.loadTeamsTab();
            });
            
            $('#wyohoops-sort').on('change', function() {
                self.currentSort = $(this).val();
                self.loadTeamsTab();
            });
            
            // Schedule tab filters
            $('#wyohoops-schedule-search, #wyohoops-schedule-gender, #wyohoops-schedule-class, #wyohoops-schedule-status, #wyohoops-schedule-conference, #wyohoops-schedule-postseason').on('change input', function() {
                self.loadScheduleTab();
            });
            
            // Compare tab
            $('#wyohoops-compare-gender').on('change', function() {
                self.loadCompareTeamOptions();
            });
            
            $('#wyohoops-compare-btn').on('click', function() {
                self.compareTeams();
            });
            
            // Modal close
            $('.wyohoops-modal-close').on('click', function() {
                $(this).closest('.wyohoops-modal').hide();
            });
        },
        
        switchTab: function(tab) {
            this.currentTab = tab;
            
            $('.wyohoops-tab-button').removeClass('active');
            $('.wyohoops-tab-button[data-tab="' + tab + '"]').addClass('active');
            
            $('.wyohoops-tab-panel').removeClass('active');
            $('#wyohoops-' + tab + '-tab').addClass('active');
            
            if (tab === 'teams') {
                this.loadTeamsTab();
            } else if (tab === 'schedule') {
                this.loadScheduleTab();
            } else if (tab === 'compare') {
                this.loadCompareTab();
            }
        },
        
        showLoading: function() {
            $('.wyohoops-loading').show();
        },
        
        hideLoading: function() {
            $('.wyohoops-loading').hide();
        },
        
        loadTeamsTab: function() {
            var self = this;
            var search = $('#wyohoops-search').val();
            
            this.showLoading();
            
            $.ajax({
                url: wyohoopsPublic.rest_url + 'rankings',
                method: 'GET',
                data: {
                    gender: self.currentGender,
                    classification: self.currentClassification,
                    orderby: self.currentSort
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wyohoopsPublic.nonce);
                },
                success: function(rankings) {
                    self.teams = rankings;
                    self.renderTeams(rankings, search);
                },
                error: function() {
                    $('#wyohoops-teams-list').html('<p>Error loading teams.</p>');
                },
                complete: function() {
                    self.hideLoading();
                }
            });
        },
        
        renderTeams: function(rankings, search) {
            var html = '';
            var filteredRankings = rankings;
            
            // Apply search filter
            if (search) {
                search = search.toLowerCase();
                filteredRankings = rankings.filter(function(team) {
                    return team.name.toLowerCase().includes(search) ||
                           team.abbreviation.toLowerCase().includes(search) ||
                           (team.location_city && team.location_city.toLowerCase().includes(search));
                });
            }
            
            if (filteredRankings.length === 0) {
                html = '<p style="text-align: center; color: var(--wyohoops-light-gray); padding: 40px;">No teams found.</p>';
            } else {
                filteredRankings.forEach(function(team) {
                    html += '<div class="wyohoops-team-card" data-team-id="' + team.id + '">';
                    html += '  <div class="wyohoops-team-header">';
                    
                    // Avatar
                    html += '    <div class="wyohoops-team-avatar" style="background-color: ' + team.primary_color + ';">';
                    if (team.logo_attachment_id) {
                        // Would need to fetch image URL - simplified for now
                        html += team.abbreviation;
                    } else {
                        html += team.abbreviation;
                    }
                    html += '    </div>';
                    
                    html += '    <div class="wyohoops-team-info">';
                    html += '      <h3>' + team.name + '</h3>';
                    html += '      <div class="wyohoops-classification">' + team.classification + '</div>';
                    html += '    </div>';
                    html += '  </div>';
                    
                    html += '  <div class="wyohoops-team-stats">';
                    html += '    <div class="wyohoops-stat-row">';
                    html += '      <span class="wyohoops-stat-label">Rank:</span>';
                    html += '      <span class="wyohoops-rank-badge">#' + team.rank + '</span>';
                    html += '    </div>';
                    html += '    <div class="wyohoops-stat-row">';
                    html += '      <span class="wyohoops-stat-label">Record:</span>';
                    html += '      <span class="wyohoops-record">' + team.wins + '-' + team.losses + '</span>';
                    html += '    </div>';
                    html += '    <div class="wyohoops-stat-row">';
                    html += '      <span class="wyohoops-stat-label">Win %:</span>';
                    html += '      <span class="wyohoops-stat-value">' + (team.win_pct * 100).toFixed(1) + '%</span>';
                    html += '    </div>';
                    html += '    <div class="wyohoops-stat-row">';
                    html += '      <span class="wyohoops-stat-label">Offensive Eff:</span>';
                    html += '      <span class="wyohoops-stat-value">' + team.offensive_efficiency + '</span>';
                    html += '    </div>';
                    html += '    <div class="wyohoops-meter">';
                    html += '      <div class="wyohoops-meter-fill" style="width: ' + team.offensive_efficiency + '%"></div>';
                    html += '    </div>';
                    html += '    <div class="wyohoops-stat-row" style="margin-top: 10px;">';
                    html += '      <span class="wyohoops-stat-label">Defensive Eff:</span>';
                    html += '      <span class="wyohoops-stat-value">' + team.defensive_efficiency + '</span>';
                    html += '    </div>';
                    html += '    <div class="wyohoops-meter">';
                    html += '      <div class="wyohoops-meter-fill" style="width: ' + team.defensive_efficiency + '%"></div>';
                    html += '    </div>';
                    html += '    <div class="wyohoops-stat-row" style="margin-top: 10px;">';
                    html += '      <span class="wyohoops-stat-label">PF/G:</span>';
                    html += '      <span class="wyohoops-stat-value">' + team.avg_points_for.toFixed(1) + '</span>';
                    html += '    </div>';
                    html += '    <div class="wyohoops-stat-row">';
                    html += '      <span class="wyohoops-stat-label">PA/G:</span>';
                    html += '      <span class="wyohoops-stat-value">' + team.avg_points_against.toFixed(1) + '</span>';
                    html += '    </div>';
                    html += '  </div>';
                    html += '</div>';
                });
            }
            
            $('#wyohoops-teams-list').html(html);
            
            // Bind click events for team cards
            $('.wyohoops-team-card').on('click', function() {
                var teamId = $(this).data('team-id');
                WyoHoopsApp.showTeamDetail(teamId);
            });
        },
        
        showTeamDetail: function(teamId) {
            var self = this;
            
            $.ajax({
                url: wyohoopsPublic.rest_url + 'teams/' + teamId,
                method: 'GET',
                data: {
                    gender: self.currentGender
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wyohoopsPublic.nonce);
                },
                success: function(data) {
                    self.renderTeamDetail(data);
                    $('#wyohoops-team-modal').show();
                },
                error: function() {
                    alert('Error loading team details.');
                }
            });
        },
        
        renderTeamDetail: function(data) {
            var team = data.team;
            var stats = data.stats;
            var recentGames = data.recent_games;
            
            var html = '<h2>' + team.name + '</h2>';
            html += '<p><strong>Classification:</strong> ' + team.classification + '</p>';
            if (team.location_city) {
                html += '<p><strong>Location:</strong> ' + team.location_city + '</p>';
            }
            
            html += '<h3>Statistics</h3>';
            html += '<table style="width: 100%; color: var(--wyohoops-text);">';
            html += '<tr><td>Record:</td><td><strong>' + stats.wins + '-' + stats.losses + '</strong></td></tr>';
            html += '<tr><td>Win %:</td><td>' + (stats.win_pct * 100).toFixed(1) + '%</td></tr>';
            html += '<tr><td>Offensive Efficiency:</td><td>' + stats.offensive_efficiency + '</td></tr>';
            html += '<tr><td>Defensive Efficiency:</td><td>' + stats.defensive_efficiency + '</td></tr>';
            html += '<tr><td>Points For/Game:</td><td>' + stats.avg_points_for.toFixed(1) + '</td></tr>';
            html += '<tr><td>Points Against/Game:</td><td>' + stats.avg_points_against.toFixed(1) + '</td></tr>';
            html += '<tr><td>Point Differential:</td><td>' + stats.point_differential + '</td></tr>';
            html += '</table>';
            
            if (recentGames.length > 0) {
                html += '<h3>Recent Games</h3>';
                html += '<ul style="list-style: none; padding: 0;">';
                recentGames.forEach(function(game) {
                    var isHome = (game.home_team_id == team.id);
                    var opponent = isHome ? 'vs Team #' + game.away_team_id : '@ Team #' + game.home_team_id;
                    var score = isHome ? game.home_score + '-' + game.away_score : game.away_score + '-' + game.home_score;
                    html += '<li style="padding: 8px 0; border-bottom: 1px solid var(--wyohoops-gray);">';
                    html += game.game_date + ' ' + opponent + ' - ' + score;
                    html += '</li>';
                });
                html += '</ul>';
            }
            
            $('#wyohoops-team-detail').html(html);
        },
        
        loadScheduleTab: function() {
            var self = this;
            var search = $('#wyohoops-schedule-search').val();
            var gender = $('#wyohoops-schedule-gender').val();
            var classification = $('#wyohoops-schedule-class').val();
            var status = $('#wyohoops-schedule-status').val();
            var conferenceOnly = $('#wyohoops-schedule-conference').is(':checked');
            var postseasonOnly = $('#wyohoops-schedule-postseason').is(':checked');
            
            this.showLoading();
            
            var params = {
                limit: 100
            };
            
            if (gender) params.gender = gender;
            if (status === 'completed') params.completed_only = true;
            if (status === 'upcoming') params.upcoming_only = true;
            if (conferenceOnly) params.conference_game = 1;
            
            $.ajax({
                url: wyohoopsPublic.rest_url + 'games',
                method: 'GET',
                data: params,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wyohoopsPublic.nonce);
                },
                success: function(games) {
                    self.renderSchedule(games, search, classification, postseasonOnly);
                },
                error: function() {
                    $('#wyohoops-schedule-list').html('<p>Error loading schedule.</p>');
                },
                complete: function() {
                    self.hideLoading();
                }
            });
        },
        
        renderSchedule: function(games, search, classification, postseasonOnly) {
            var html = '';
            var filteredGames = games;
            
            // Apply filters
            if (search) {
                search = search.toLowerCase();
                filteredGames = games.filter(function(game) {
                    return game.home_team_name.toLowerCase().includes(search) ||
                           game.away_team_name.toLowerCase().includes(search);
                });
            }
            
            if (postseasonOnly) {
                filteredGames = filteredGames.filter(function(game) {
                    return game.postseason_round;
                });
            }
            
            if (filteredGames.length === 0) {
                html = '<p style="text-align: center; color: var(--wyohoops-light-gray); padding: 40px;">No games found.</p>';
            } else {
                filteredGames.forEach(function(game) {
                    var isCompleted = (game.home_score !== null && game.away_score !== null);
                    
                    html += '<div class="wyohoops-game-card">';
                    html += '  <div class="wyohoops-game-date">' + game.game_date + '</div>';
                    html += '  <div class="wyohoops-game-matchup">';
                    html += '    <span class="wyohoops-team-name">' + game.home_team_name + '</span>';
                    html += '    <span class="wyohoops-vs">vs</span>';
                    html += '    <span class="wyohoops-team-name">' + game.away_team_name + '</span>';
                    html += '  </div>';
                    
                    if (isCompleted) {
                        html += '  <div class="wyohoops-game-score">' + game.home_score + ' - ' + game.away_score + '</div>';
                    } else {
                        html += '  <div class="wyohoops-game-score upcoming">Scheduled</div>';
                    }
                    
                    html += '</div>';
                });
            }
            
            $('#wyohoops-schedule-list').html(html);
        },
        
        loadCompareTab: function() {
            this.loadCompareTeamOptions();
        },
        
        loadCompareTeamOptions: function() {
            var self = this;
            var gender = $('#wyohoops-compare-gender').val();
            
            $.ajax({
                url: wyohoopsPublic.rest_url + 'teams',
                method: 'GET',
                data: {
                    is_active: 1
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wyohoopsPublic.nonce);
                },
                success: function(teams) {
                    var options = '<option value="">Select Team...</option>';
                    teams.forEach(function(team) {
                        options += '<option value="' + team.id + '">' + team.name + ' (' + team.classification + ')</option>';
                    });
                    
                    $('#wyohoops-compare-team-a, #wyohoops-compare-team-b').html(options);
                }
            });
        },
        
        compareTeams: function() {
            var self = this;
            var teamA = $('#wyohoops-compare-team-a').val();
            var teamB = $('#wyohoops-compare-team-b').val();
            var gender = $('#wyohoops-compare-gender').val();
            
            if (!teamA || !teamB) {
                alert('Please select both teams to compare.');
                return;
            }
            
            if (teamA === teamB) {
                alert('Please select two different teams.');
                return;
            }
            
            this.showLoading();
            
            $.ajax({
                url: wyohoopsPublic.rest_url + 'compare',
                method: 'GET',
                data: {
                    team_a: teamA,
                    team_b: teamB,
                    gender: gender
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wyohoopsPublic.nonce);
                },
                success: function(comparison) {
                    self.renderComparison(comparison);
                    $('#wyohoops-compare-results').show();
                },
                error: function() {
                    alert('Error comparing teams.');
                },
                complete: function() {
                    self.hideLoading();
                }
            });
        },
        
        renderComparison: function(comparison) {
            var teamA = comparison.team_a;
            var teamB = comparison.team_b;
            var edges = comparison.edges;
            
            var html = '<div class="wyohoops-compare-grid">';
            
            // Team A
            html += '<div class="wyohoops-compare-team">';
            html += '  <h3>' + teamA.name + '</h3>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">Record</div>';
            html += '    <div class="wyohoops-compare-stat-value' + (edges.win_pct === 'A' ? ' advantage' : '') + '">' + teamA.wins + '-' + teamA.losses + '</div>';
            html += '  </div>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">Win %</div>';
            html += '    <div class="wyohoops-compare-stat-value' + (edges.win_pct === 'A' ? ' advantage' : '') + '">' + (teamA.win_pct * 100).toFixed(1) + '%</div>';
            html += '  </div>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">Offensive Eff</div>';
            html += '    <div class="wyohoops-compare-stat-value' + (edges.offensive === 'A' ? ' advantage' : '') + '">' + teamA.offensive_efficiency + '</div>';
            html += '  </div>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">Defensive Eff</div>';
            html += '    <div class="wyohoops-compare-stat-value' + (edges.defensive === 'A' ? ' advantage' : '') + '">' + teamA.defensive_efficiency + '</div>';
            html += '  </div>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">PF/G</div>';
            html += '    <div class="wyohoops-compare-stat-value">' + teamA.avg_points_for.toFixed(1) + '</div>';
            html += '  </div>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">PA/G</div>';
            html += '    <div class="wyohoops-compare-stat-value">' + teamA.avg_points_against.toFixed(1) + '</div>';
            html += '  </div>';
            html += '</div>';
            
            // Divider
            html += '<div class="wyohoops-compare-divider"></div>';
            
            // Team B
            html += '<div class="wyohoops-compare-team">';
            html += '  <h3>' + teamB.name + '</h3>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">Record</div>';
            html += '    <div class="wyohoops-compare-stat-value' + (edges.win_pct === 'B' ? ' advantage' : '') + '">' + teamB.wins + '-' + teamB.losses + '</div>';
            html += '  </div>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">Win %</div>';
            html += '    <div class="wyohoops-compare-stat-value' + (edges.win_pct === 'B' ? ' advantage' : '') + '">' + (teamB.win_pct * 100).toFixed(1) + '%</div>';
            html += '  </div>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">Offensive Eff</div>';
            html += '    <div class="wyohoops-compare-stat-value' + (edges.offensive === 'B' ? ' advantage' : '') + '">' + teamB.offensive_efficiency + '</div>';
            html += '  </div>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">Defensive Eff</div>';
            html += '    <div class="wyohoops-compare-stat-value' + (edges.defensive === 'B' ? ' advantage' : '') + '">' + teamB.defensive_efficiency + '</div>';
            html += '  </div>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">PF/G</div>';
            html += '    <div class="wyohoops-compare-stat-value">' + teamB.avg_points_for.toFixed(1) + '</div>';
            html += '  </div>';
            html += '  <div class="wyohoops-compare-stat">';
            html += '    <div class="wyohoops-compare-stat-label">PA/G</div>';
            html += '    <div class="wyohoops-compare-stat-value">' + teamB.avg_points_against.toFixed(1) + '</div>';
            html += '  </div>';
            html += '</div>';
            
            html += '</div>';
            
            // Matchup Preview
            html += '<div class="wyohoops-matchup-preview">';
            html += '  <h4>Matchup Preview</h4>';
            html += '  <div class="wyohoops-edge-indicator">';
            html += '    <span class="wyohoops-edge-label">Record Advantage:</span>';
            html += '    <span class="wyohoops-edge-value">' + (edges.win_pct === 'A' ? teamA.name : edges.win_pct === 'B' ? teamB.name : 'Tie') + '</span>';
            html += '  </div>';
            html += '  <div class="wyohoops-edge-indicator">';
            html += '    <span class="wyohoops-edge-label">Offensive Edge:</span>';
            html += '    <span class="wyohoops-edge-value">' + (edges.offensive === 'A' ? teamA.name : edges.offensive === 'B' ? teamB.name : 'Tie') + '</span>';
            html += '  </div>';
            html += '  <div class="wyohoops-edge-indicator">';
            html += '    <span class="wyohoops-edge-label">Defensive Edge:</span>';
            html += '    <span class="wyohoops-edge-value">' + (edges.defensive === 'A' ? teamA.name : edges.defensive === 'B' ? teamB.name : 'Tie') + '</span>';
            html += '  </div>';
            html += '</div>';
            
            $('#wyohoops-compare-results').html(html);
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        if ($('.wyohoops-gamedb').length) {
            WyoHoopsApp.init();
        }
    });
    
})(jQuery);
