/**
 * Building Visualizer JavaScript
 * Handles 3D building rendering and user interactions
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
                wainscottColor: null
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
            
            // Draw sky gradient
            const skyGradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
            skyGradient.addColorStop(0, '#87CEEB');
            skyGradient.addColorStop(1, '#E0F6FF');
            ctx.fillStyle = skyGradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Draw ground
            ctx.fillStyle = '#8B7355';
            ctx.fillRect(0, canvas.height - 80, canvas.width, 80);
            
            // Calculate building dimensions on canvas
            const scale = 6; // pixels per foot
            const offsetX = canvas.width / 2;
            const offsetY = canvas.height - 100;
            
            // Calculate roof peak height
            const roofPeakHeight = (this.params.width / 2) * (this.params.roofPitch / 12);
            
            // Draw building (isometric-style 3D view)
            this.drawBuilding3D(ctx, offsetX, offsetY, scale, roofPeakHeight);
            
            // Update info display
            this.updateInfo();
        }
        
        drawBuilding3D(ctx, offsetX, offsetY, scale, roofPeakHeight) {
            const width = this.params.width * scale;
            const length = this.params.length * scale;
            const wallHeight = this.params.wallHeight * scale;
            const wainscottHeight = 3 * scale; // 3 feet
            
            // Isometric projection angles
            const isoAngle = Math.PI / 6; // 30 degrees
            const isoX = Math.cos(isoAngle);
            const isoY = Math.sin(isoAngle);
            
            // Calculate vertices
            const front = {
                bottomLeft: { x: offsetX - width / 2, y: offsetY },
                bottomRight: { x: offsetX + width / 2, y: offsetY },
                topLeft: { x: offsetX - width / 2, y: offsetY - wallHeight },
                topRight: { x: offsetX + width / 2, y: offsetY - wallHeight }
            };
            
            const back = {
                bottomLeft: { x: front.bottomLeft.x - length * isoX, y: front.bottomLeft.y - length * isoY },
                bottomRight: { x: front.bottomRight.x - length * isoX, y: front.bottomRight.y - length * isoY },
                topLeft: { x: front.topLeft.x - length * isoX, y: front.topLeft.y - length * isoY },
                topRight: { x: front.topRight.x - length * isoX, y: front.topRight.y - length * isoY }
            };
            
            // Roof peak points
            const roofPeak = roofPeakHeight * scale;
            const frontPeak = { x: offsetX, y: offsetY - wallHeight - roofPeak };
            const backPeak = { x: offsetX - length * isoX, y: offsetY - length * isoY - wallHeight - roofPeak };
            
            // Draw left side (with wainscott)
            ctx.fillStyle = this.getRgbColor(this.params.sidingColor);
            ctx.beginPath();
            ctx.moveTo(front.bottomLeft.x, front.bottomLeft.y);
            ctx.lineTo(back.bottomLeft.x, back.bottomLeft.y);
            ctx.lineTo(back.topLeft.x, back.topLeft.y);
            ctx.lineTo(front.topLeft.x, front.topLeft.y);
            ctx.closePath();
            ctx.fill();
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 1;
            ctx.stroke();
            
            // Draw left wainscott
            ctx.fillStyle = this.getRgbColor(this.params.wainscottColor);
            ctx.beginPath();
            ctx.moveTo(front.bottomLeft.x, front.bottomLeft.y);
            ctx.lineTo(back.bottomLeft.x, back.bottomLeft.y);
            ctx.lineTo(back.bottomLeft.x, back.bottomLeft.y - wainscottHeight);
            ctx.lineTo(front.bottomLeft.x, front.bottomLeft.y - wainscottHeight);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            
            // Draw front side (with wainscott)
            ctx.fillStyle = this.getRgbColor(this.params.sidingColor);
            ctx.beginPath();
            ctx.moveTo(front.bottomLeft.x, front.bottomLeft.y);
            ctx.lineTo(front.bottomRight.x, front.bottomRight.y);
            ctx.lineTo(front.topRight.x, front.topRight.y);
            ctx.lineTo(front.topLeft.x, front.topLeft.y);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            
            // Draw front wainscott
            ctx.fillStyle = this.getRgbColor(this.params.wainscottColor);
            ctx.beginPath();
            ctx.moveTo(front.bottomLeft.x, front.bottomLeft.y);
            ctx.lineTo(front.bottomRight.x, front.bottomRight.y);
            ctx.lineTo(front.bottomRight.x, front.bottomRight.y - wainscottHeight);
            ctx.lineTo(front.bottomLeft.x, front.bottomLeft.y - wainscottHeight);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            
            // Draw left roof
            ctx.fillStyle = this.getRgbColor(this.params.roofingColor);
            ctx.beginPath();
            ctx.moveTo(front.topLeft.x, front.topLeft.y);
            ctx.lineTo(frontPeak.x, frontPeak.y);
            ctx.lineTo(backPeak.x, backPeak.y);
            ctx.lineTo(back.topLeft.x, back.topLeft.y);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            
            // Draw right roof (darker shade)
            const darkerRoofColor = this.darkenColor(this.params.roofingColor, 0.7);
            ctx.fillStyle = darkerRoofColor;
            ctx.beginPath();
            ctx.moveTo(front.topRight.x, front.topRight.y);
            ctx.lineTo(frontPeak.x, frontPeak.y);
            ctx.lineTo(backPeak.x, backPeak.y);
            ctx.lineTo(back.topRight.x, back.topRight.y);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            
            // Draw roof ridge line
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(frontPeak.x, frontPeak.y);
            ctx.lineTo(backPeak.x, backPeak.y);
            ctx.stroke();
        }
        
        getRgbColor(colorObj) {
            if (!colorObj || !colorObj.rgb) return '#999';
            return `rgb(${colorObj.rgb})`;
        }
        
        darkenColor(colorObj, factor) {
            if (!colorObj || !colorObj.rgb) return '#666';
            const rgb = colorObj.rgb.split(',').map(v => Math.floor(parseInt(v.trim()) * factor));
            return `rgb(${rgb.join(',')})`;
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
