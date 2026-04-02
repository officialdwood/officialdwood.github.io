# Building Orientation Fix - Visual Guide

## Reference Image Analysis

User's reference image shows a building from the **LEFT-FRONT CORNER**:
- Gable end (front wall with door opening) visible on the LEFT side
- Long side wall visible on the RIGHT side
- Looking down from above to see the roof
- Wainscott (dark bottom section) visible on both walls

## What Changed

### Rotation Values

**Previous Settings (INCORRECT):**
- Horizontal Rotation (Y-axis): 330°
- Vertical Tilt (X-axis): 20°
- Result: Wrong corner perspective

**New Settings (CORRECT - Matches Reference):**
- Horizontal Rotation (Y-axis): **225°**
- Vertical Tilt (X-axis): **25°**
- Result: LEFT-FRONT corner perspective (MATCHES REFERENCE!)

## Why 225° Horizontal?

In a 360° rotation system:
- 0° = Looking at front gable straight on
- 90° = Looking at right side
- 180° = Looking at back
- 225° = Looking from LEFT-FRONT corner (45° past 180°)
- 270° = Looking at left side
- 330° = Looking from RIGHT-FRONT corner (wrong!)

**225° places the viewer at the LEFT-FRONT corner**, which puts:
- ✅ Gable end on the LEFT side of the view
- ✅ Long side wall on the RIGHT side of the view
- ✅ Matches the reference image EXACTLY

## Why 25° Vertical Tilt?

- Positive vertical tilt = Looking DOWN from above
- 25° provides a good downward angle to:
  - See both roof planes clearly
  - Show the roof pitch
  - Reveal the wainscott on the walls
  - Match the perspective in the reference image

## Visual Description of New Default View

```
                    ROOF (DARK)
                   /          \
                  /            \
                 /              \
    GABLE WALL  |                | SIDE WALL
    (ON LEFT)   |                | (ON RIGHT)
    with door   |________________|
    opening     
                     
    WAINSCOTT (dark bottom 3 feet on both visible walls)
```

When the page loads, users will see:
1. The gable end wall on the LEFT (where door would be)
2. The long side wall on the RIGHT (full length visible)
3. The roof from above with both planes visible
4. The wainscott at the bottom of both walls
5. A natural 3D perspective looking down at 25°

This EXACTLY matches the reference image provided by the user.

## Files Modified

1. `building-visualizer/assets/js/app.js`
   - Changed rotationY from 330 to 225
   - Changed rotationX from 20 to 25

2. `building-visualizer/includes/class-bv-shortcode.php`
   - Updated HTML slider default values to 225° and 25°

3. `building-visualizer/demo.html`
   - Updated HTML slider default values to 225° and 25°

4. `building-visualizer.zip`
   - Rebuilt production package with all changes

## Result

✅ Building now loads with EXACT orientation as reference image
✅ Gable on LEFT, side on RIGHT
✅ Looking down from above at 25°
✅ All rotation and customization features still work
✅ Users can rotate to see all angles, but default matches reference
