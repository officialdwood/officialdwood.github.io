/**
 * Building Visualizer JavaScript
 * Simple 2D front view rendering with color customization
 */

(function($) {
    'use strict';
    
    // Building Visualizer Class
    class BuildingVisualizer {
        constructor() {
            this.canvas = document.getElementById('bv-canvas');
            if (!this.canvas) return;
            
            this.ctx = this.canvas.getContext('2d');
            this.colors = BVConfig.colors || {};
            
            // Building parameters
            this.params = {
                width: parseFloat($('#bv-width').val()) || 20,
                length: parseFloat($('#bv-length').val()) || 40,
                wallHeight: parseFloat($('#bv-wall-height').val()) || 12,
                roofPitch: parseFloat($('#bv-roof-pitch').val()) || 4,
                roofingColor: null,
                sidingColor: null,
                wainscottColor: null,
                wainscottEnabled: true,
                wainscottHeight: 3
            };
            
            this.init();
        }
        
        init() {
            this.populateColorSelects();
            this.setDefaultColors();
            this.attachEventListeners();
            this.render();
        }
        
        populateColorSelects() {
            // Populate roofing colors
            const roofingSelect = $('#bv-roofing-color');
            if (this.colors.roofing) {
                this.colors.roofing.forEach((color, index) => {
                    roofingSelect.append(`<option value="${index}">${color.name}</option>`);
                });
            }
            
            // Populate siding colors
            const sidingSelect = $('#bv-siding-color');
            if (this.colors.siding) {
                this.colors.siding.forEach((color, index) => {
                    sidingSelect.append(`<option value="${index}">${color.name}</option>`);
                });
            }
            
            // Populate wainscott colors
            const wainscottSelect = $('#bv-wainscott-color');
            if (this.colors.wainscott) {
                this.colors.wainscott.forEach((color, index) => {
                    wainscottSelect.append(`<option value="${index}">${color.name}</option>`);
                });
            }
        }
        
        setDefaultColors() {
            if (this.colors.roofing && this.colors.roofing.length > 0) {
                this.params.roofingColor = this.colors.roofing[0];
            }
            if (this.colors.siding && this.colors.siding.length > 0) {
                this.params.sidingColor = this.colors.siding[0];
            }
            if (this.colors.wainscott && this.colors.wainscott.length > 0) {
                this.params.wainscottColor = this.colors.wainscott[0];
            }
        }
        
        attachEventListeners() {
            const self = this;
            
            // Dimension inputs
            $('#bv-width, #bv-length, #bv-wall-height, #bv-roof-pitch').on('input', function() {
                self.params.width = parseFloat($('#bv-width').val()) || 20;
                self.params.length = parseFloat($('#bv-length').val()) || 40;
                self.params.wallHeight = parseFloat($('#bv-wall-height').val()) || 12;
                self.params.roofPitch = parseFloat($('#bv-roof-pitch').val()) || 4;
                self.render();
            });
            
            // Wainscott controls
            $('#bv-wainscott-enabled').on('change', function() {
                self.params.wainscottEnabled = $(this).is(':checked');
                $('#bv-wainscott-controls, #bv-wainscott-color-group').toggle(self.params.wainscottEnabled);
                self.render();
            });
            
            $('#bv-wainscott-height').on('input', function() {
                self.params.wainscottHeight = parseFloat($(this).val()) || 3;
                self.render();
            });
            
            // Color selects
            $('#bv-roofing-color').on('change', function() {
                const index = parseInt($(this).val());
                self.params.roofingColor = self.colors.roofing[index];
                self.render();
            });
            
            $('#bv-siding-color').on('change', function() {
                const index = parseInt($(this).val());
                self.params.sidingColor = self.colors.siding[index];
                self.render();
            });
            
            $('#bv-wainscott-color').on('change', function() {
                const index = parseInt($(this).val());
                self.params.wainscottColor = self.colors.wainscott[index];
                self.render();
            });
            
            // Download button
            $('#bv-download').on('click', function() {
                self.downloadImage();
            });
        }
        
        render() {
            const ctx = this.ctx;
            const canvas = this.canvas;
            
            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Draw very light gray background
            ctx.fillStyle = '#FAFAFA';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Draw simple 2D front view of building
            this.drawBuilding2D(ctx, canvas);
            
            // Update info display
            this.updateInfo();
        }
        
        drawBuilding2D(ctx, canvas) {
            // Simple 2D front view - gable wall with roof
            const scale = 15; // pixels per foot
            const buildingWidth = this.params.width * scale;
            const wallHeight = this.params.wallHeight * scale;
            const roofPeakHeight = (this.params.width / 2) * (this.params.roofPitch / 12) * scale;
            
            // Center the building
            const centerX = canvas.width / 2;
            const groundY = canvas.height - 100;
            const topOfWallY = groundY - wallHeight;
            const roofPeakY = topOfWallY - roofPeakHeight;
            
            const leftX = centerX - buildingWidth / 2;
            const rightX = centerX + buildingWidth / 2;
            
            // Get colors
            const roofingColor = this.params.roofingColor ? 
                `rgb(${this.params.roofingColor.r}, ${this.params.roofingColor.g}, ${this.params.roofingColor.b})` : 
                '#555555';
            const sidingColor = this.params.sidingColor ? 
                `rgb(${this.params.sidingColor.r}, ${this.params.sidingColor.g}, ${this.params.sidingColor.b})` : 
                '#E0E0E0';
            const wainscottColor = this.params.wainscottColor ? 
                `rgb(${this.params.wainscottColor.r}, ${this.params.wainscottColor.g}, ${this.params.wainscottColor.b})` : 
                '#8B4513';
            
            // Draw roof
            ctx.beginPath();
            ctx.moveTo(leftX, topOfWallY); // Left wall top
            ctx.lineTo(centerX, roofPeakY); // Peak
            ctx.lineTo(rightX, topOfWallY); // Right wall top
            ctx.closePath();
            ctx.fillStyle = roofingColor;
            ctx.fill();
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.stroke();
            
            // Draw ridge line
            ctx.beginPath();
            ctx.moveTo(centerX, roofPeakY);
            ctx.lineTo(centerX, roofPeakY + 5);
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 3;
            ctx.stroke();
            
            // Draw main wall (siding)
            ctx.fillStyle = sidingColor;
            ctx.fillRect(leftX, topOfWallY, buildingWidth, wallHeight);
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.strokeRect(leftX, topOfWallY, buildingWidth, wallHeight);
            
            // Draw wainscott if enabled
            if (this.params.wainscottEnabled) {
                const wainscottHeight = this.params.wainscottHeight * scale;
                const wainscottY = groundY - wainscottHeight;
                
                ctx.fillStyle = wainscottColor;
                ctx.fillRect(leftX, wainscottY, buildingWidth, wainscottHeight);
                
                // Draw horizontal line separating wainscott from siding
                ctx.beginPath();
                ctx.moveTo(leftX, wainscottY);
                ctx.lineTo(rightX, wainscottY);
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 2;
                ctx.stroke();
            }
            
            // Draw vertical siding lines for texture
            ctx.strokeStyle = 'rgba(0, 0, 0, 0.1)';
            ctx.lineWidth = 1;
            for (let x = leftX + 20; x < rightX; x += 20) {
                ctx.beginPath();
                ctx.moveTo(x, topOfWallY);
                ctx.lineTo(x, groundY);
                ctx.stroke();
            }
            
            // Draw door outline (optional visual element)
            const doorWidth = 40;
            const doorHeight = 80;
            const doorX = centerX - doorWidth / 2;
            const doorY = groundY - doorHeight;
            
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.strokeRect(doorX, doorY, doorWidth, doorHeight);
        }
        
        updateInfo() {
            $('#bv-info-size').text(`${this.params.width}' x ${this.params.length}'`);
            $('#bv-info-wall').text(`${this.params.wallHeight}'`);
            $('#bv-info-pitch').text(`${this.params.roofPitch}:12`);
        }
        
        downloadImage() {
            const link = document.createElement('a');
            link.download = `building-${this.params.width}x${this.params.length}-${Date.now()}.png`;
            link.href = this.canvas.toDataURL('image/png');
            link.click();
        }
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#bv-canvas').length) {
            new BuildingVisualizer();
        }
    });
    
})(jQuery);
