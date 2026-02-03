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
                wainscottColor: null,
                wainscottEnabled: true,
                wainscottHeight: 3,
                rotationY: 45, // degrees - horizontal rotation (around vertical axis)
                rotationX: 20, // degrees - vertical tilt (pitch)
                zoom: 1 // scale factor
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
            
            // Rotation controls - Y axis (horizontal)
            $('#bv-rotation-y').on('input', function() {
                self.params.rotationY = parseFloat($(this).val());
                $('#bv-rotation-y-value').text(self.params.rotationY + '°');
                self.render();
            });
            
            // Rotation controls - X axis (vertical tilt)
            $('#bv-rotation-x').on('input', function() {
                self.params.rotationX = parseFloat($(this).val());
                $('#bv-rotation-x-value').text(self.params.rotationX + '°');
                self.render();
            });
            
            // Zoom controls
            $('#bv-zoom').on('input', function() {
                self.params.zoom = parseFloat($(this).val());
                $('#bv-zoom-value').text(Math.round(self.params.zoom * 100) + '%');
                self.render();
            });
            
            $('#bv-zoom-in').on('click', function() {
                self.params.zoom = Math.min(3, self.params.zoom + 0.2);
                $('#bv-zoom').val(self.params.zoom);
                $('#bv-zoom-value').text(Math.round(self.params.zoom * 100) + '%');
                self.render();
            });
            
            $('#bv-zoom-out').on('click', function() {
                self.params.zoom = Math.max(0.5, self.params.zoom - 0.2);
                $('#bv-zoom').val(self.params.zoom);
                $('#bv-zoom-value').text(Math.round(self.params.zoom * 100) + '%');
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
            
            // Draw light gray/off-white background
            ctx.fillStyle = '#F5F5F5';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Draw ground
            ctx.fillStyle = '#D3D3D3';
            ctx.fillRect(0, canvas.height - 80, canvas.width, 80);
            
            // Calculate building dimensions on canvas
            const baseScale = 6; // pixels per foot at 100% zoom
            const scale = baseScale * this.params.zoom;
            
            // Calculate roof peak height
            const roofPeakHeight = (this.params.width / 2) * (this.params.roofPitch / 12);
            const totalHeight = this.params.wallHeight + roofPeakHeight;
            
            // Center the building horizontally and vertically (accounting for total height)
            const offsetX = canvas.width / 2;
            // Position so the building (including roof) is centered vertically in the available space above ground
            const availableHeight = canvas.height - 80; // space above ground
            const buildingHeightPixels = totalHeight * scale;
            const offsetY = (availableHeight + buildingHeightPixels) / 2;
            
            // Draw building (isometric-style 3D view with rotation)
            this.drawBuilding3D(ctx, offsetX, offsetY, scale, roofPeakHeight);
            
            // Draw scale indicator
            this.drawScaleIndicator(ctx, scale);
            
            // Update info display
            this.updateInfo();
        }
        
        drawBuilding3D(ctx, offsetX, offsetY, scale, roofPeakHeight) {
            const width = this.params.width * scale;
            const length = this.params.length * scale;
            const wallHeight = this.params.wallHeight * scale;
            const wainscottHeight = this.params.wainscottHeight * scale;
            
            // Convert rotations to radians
            const rotationYRad = (this.params.rotationY * Math.PI) / 180; // horizontal spin
            const rotationXRad = (this.params.rotationX * Math.PI) / 180; // vertical tilt
            
            // Isometric projection angles
            const isoAngle = Math.PI / 6; // 30 degrees for isometric view
            
            // Helper function to rotate and project a point
            const projectPoint = (x, y, z) => {
                // First rotate around Y axis (horizontal spin)
                let rotX = x * Math.cos(rotationYRad) - y * Math.sin(rotationYRad);
                let rotY = x * Math.sin(rotationYRad) + y * Math.cos(rotationYRad);
                let rotZ = z;
                
                // Then rotate around X axis (vertical tilt/pitch)
                const tempY = rotY;
                const tempZ = rotZ;
                rotY = tempY * Math.cos(rotationXRad) - tempZ * Math.sin(rotationXRad);
                rotZ = tempY * Math.sin(rotationXRad) + tempZ * Math.cos(rotationXRad);
                
                // Isometric projection - NOTE: using -rotZ for correct orientation (roof up, ground down)
                const isoX = rotX * Math.cos(isoAngle) - rotY * Math.cos(isoAngle);
                const isoY = rotX * Math.sin(isoAngle) + rotY * Math.sin(isoAngle) - rotZ;
                
                return {
                    x: offsetX + isoX,
                    y: offsetY + isoY
                };
            };
            
            // Define the 8 corners of the building box (before roof)
            const corners = {
                // Bottom corners (z = 0)
                fbl: projectPoint(-width/2, -length/2, 0),  // front bottom left
                fbr: projectPoint(width/2, -length/2, 0),   // front bottom right
                bbl: projectPoint(-width/2, length/2, 0),   // back bottom left
                bbr: projectPoint(width/2, length/2, 0),    // back bottom right
                
                // Top corners (z = wallHeight)
                ftl: projectPoint(-width/2, -length/2, -wallHeight), // front top left
                ftr: projectPoint(width/2, -length/2, -wallHeight),  // front top right
                btl: projectPoint(-width/2, length/2, -wallHeight),  // back top left
                btr: projectPoint(width/2, length/2, -wallHeight),   // back top right
                
                // Wainscott top line (z = wainscottHeight)
                fwl: projectPoint(-width/2, -length/2, -wainscottHeight),
                fwr: projectPoint(width/2, -length/2, -wainscottHeight),
                bwl: projectPoint(-width/2, length/2, -wainscottHeight),
                bwr: projectPoint(width/2, length/2, -wainscottHeight)
            };
            
            // Roof peak points
            const roofPeak = roofPeakHeight * scale;
            const frontPeak = projectPoint(0, -length/2, -wallHeight - roofPeak);
            const backPeak = projectPoint(0, length/2, -wallHeight - roofPeak);
            
            // Determine which faces are visible based on rotationY
            const rot = this.params.rotationY % 360;
            const showFront = rot >= 315 || rot < 135;
            const showBack = rot >= 135 && rot < 315;
            const showLeft = rot >= 45 && rot < 225;
            const showRight = rot >= 225 || rot < 45;
            
            // Draw faces in order (back to front for proper layering)
            const faces = [];
            
            // Back wall
            if (showBack) {
                faces.push({
                    type: 'wall',
                    points: [corners.bbl, corners.bbr, corners.btr, corners.btl],
                    color: this.getRgbColor(this.params.sidingColor),
                    depth: 2
                });
                if (this.params.wainscottEnabled) {
                    faces.push({
                        type: 'wainscott',
                        points: [corners.bbl, corners.bbr, corners.bwr, corners.bwl],
                        color: this.getRgbColor(this.params.wainscottColor),
                        depth: 2.1
                    });
                }
            }
            
            // Right wall
            if (showRight) {
                faces.push({
                    type: 'wall',
                    points: [corners.fbr, corners.bbr, corners.btr, corners.ftr],
                    color: this.darkenColor(this.params.sidingColor, 0.7),
                    depth: 1.5
                });
                if (this.params.wainscottEnabled) {
                    faces.push({
                        type: 'wainscott',
                        points: [corners.fbr, corners.bbr, corners.bwr, corners.fwr],
                        color: this.darkenColor(this.params.wainscottColor, 0.75),
                        depth: 1.6
                    });
                }
            }
            
            // Left wall
            if (showLeft) {
                faces.push({
                    type: 'wall',
                    points: [corners.bbl, corners.fbl, corners.ftl, corners.btl],
                    color: this.darkenColor(this.params.sidingColor, 0.7),
                    depth: 1.5
                });
                if (this.params.wainscottEnabled) {
                    faces.push({
                        type: 'wainscott',
                        points: [corners.bbl, corners.fbl, corners.fwl, corners.bwl],
                        color: this.darkenColor(this.params.wainscottColor, 0.75),
                        depth: 1.6
                    });
                }
            }
            
            // Front wall
            if (showFront) {
                faces.push({
                    type: 'wall',
                    points: [corners.fbl, corners.fbr, corners.ftr, corners.ftl],
                    color: this.getRgbColor(this.params.sidingColor),
                    depth: 1
                });
                if (this.params.wainscottEnabled) {
                    faces.push({
                        type: 'wainscott',
                        points: [corners.fbl, corners.fbr, corners.fwr, corners.fwl],
                        color: this.getRgbColor(this.params.wainscottColor),
                        depth: 1.1
                    });
                }
                // Front gable end (triangle)
                faces.push({
                    type: 'gable',
                    points: [corners.ftl, corners.ftr, frontPeak],
                    color: this.getRgbColor(this.params.sidingColor),
                    depth: 0.9
                });
            }
            
            // Back gable end (triangle)
            if (showBack) {
                faces.push({
                    type: 'gable',
                    points: [corners.btl, corners.btr, backPeak],
                    color: this.getRgbColor(this.params.sidingColor),
                    depth: 2.2
                });
            }
            
            // Roof faces
            // Left roof
            faces.push({
                type: 'roof',
                points: [corners.ftl, frontPeak, backPeak, corners.btl],
                color: this.getRgbColor(this.params.roofingColor),
                depth: 0.5
            });
            
            // Right roof
            faces.push({
                type: 'roof',
                points: [corners.ftr, frontPeak, backPeak, corners.btr],
                color: this.darkenColor(this.params.roofingColor, 0.7),
                depth: 0.5
            });
            
            // Sort faces by depth (back to front)
            faces.sort((a, b) => b.depth - a.depth);
            
            // Draw all faces
            faces.forEach(face => {
                ctx.fillStyle = face.color;
                ctx.beginPath();
                ctx.moveTo(face.points[0].x, face.points[0].y);
                for (let i = 1; i < face.points.length; i++) {
                    ctx.lineTo(face.points[i].x, face.points[i].y);
                }
                ctx.closePath();
                ctx.fill();
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 1.5;
                ctx.stroke();
            });
            
            // Draw roof ridge line
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(frontPeak.x, frontPeak.y);
            ctx.lineTo(backPeak.x, backPeak.y);
            ctx.stroke();
        }
        
        drawScaleIndicator(ctx, scale) {
            // Draw scale indicator in bottom left corner
            const padding = 20;
            const lineLength = 60; // pixels
            const actualFeet = lineLength / scale; // how many feet this represents
            
            // Round to nice number
            let displayFeet = 1;
            if (actualFeet > 0.5 && actualFeet < 2) displayFeet = 1;
            else if (actualFeet >= 2 && actualFeet < 5) displayFeet = 2;
            else if (actualFeet >= 5 && actualFeet < 10) displayFeet = 5;
            else if (actualFeet >= 10 && actualFeet < 20) displayFeet = 10;
            else if (actualFeet >= 20) displayFeet = 20;
            
            const displayLength = displayFeet * scale;
            
            // Draw scale bar
            const startX = padding;
            const startY = this.canvas.height - padding;
            
            ctx.fillStyle = '#000';
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            
            // Horizontal line
            ctx.beginPath();
            ctx.moveTo(startX, startY);
            ctx.lineTo(startX + displayLength, startY);
            ctx.stroke();
            
            // End ticks
            ctx.beginPath();
            ctx.moveTo(startX, startY - 5);
            ctx.lineTo(startX, startY + 5);
            ctx.moveTo(startX + displayLength, startY - 5);
            ctx.lineTo(startX + displayLength, startY + 5);
            ctx.stroke();
            
            // Label
            ctx.font = 'bold 12px Arial';
            ctx.fillStyle = '#000';
            ctx.textAlign = 'center';
            ctx.fillText(`${displayFeet}'`, startX + displayLength / 2, startY - 10);
            
            // Scale ratio
            ctx.font = '10px Arial';
            ctx.fillText(`Scale: ${Math.round(this.params.zoom * 100)}%`, startX + displayLength / 2, startY + 18);
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
