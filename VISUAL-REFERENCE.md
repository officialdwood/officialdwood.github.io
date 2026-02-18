# WyoHoops UI Redesign - Visual Reference

## What You Get

This document provides a visual reference for the completed UI/UX redesign.

---

## 📱 Screen Flow

```
┌─────────────────────────────────────────────┐
│                                             │
│           🏀 WYOHOOPS LOGO 🏀               │
│         (Centered, Configurable)            │
│                                             │
├─────────────────────────────────────────────┤
│                                             │
│  [Home] [Teams] [Rankings] [Players] [Stats]│
│   ^^^^                                      │
│  Active Tab (Metallic Gold Underline)      │
│                                             │
├─────────────────────────────────────────────┤
│                                             │
│          HOME SCREEN (Default)              │
│                                             │
│  📊 Top 5 Teams                             │
│  ┌──────────────────────────────────────┐  │
│  │ 1. Sheridan (14-1)      [98 Off]    │  │
│  │ 2. Green River (14-4)   [95 Off]    │  │
│  │ 3. Rock Springs (14-4)  [94 Off]    │  │
│  │ 4. Lovell (17-2)        [96 Off]    │  │
│  │ 5. Douglas (16-4)       [93 Off]    │  │
│  └──────────────────────────────────────┘  │
│                                             │
│  👤 Top 5 Players                           │
│  ┌──────────────────────────────────────┐  │
│  │ [Photo] John Doe                     │  │
│  │         Sheridan | Senior            │  │
│  │         PPG: 24.5 | Rating: 98       │  │
│  ├──────────────────────────────────────┤  │
│  │ [Photo] Jane Smith                   │  │
│  │         Lovell | Junior              │  │
│  │         PPG: 22.1 | Rating: 96       │  │
│  └──────────────────────────────────────┘  │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 🎨 Color Scheme

```
┌─────────────────────────────────────────────┐
│                                             │
│   Metallic Gold: #FFD700, #C8A100, #B8860B │
│   ████████████████████████████████████████  │
│                                             │
│   Light Background: #F5F5F5                 │
│   ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░  │
│                                             │
│   White Background: #FFFFFF                 │
│   ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓  │
│                                             │
│   Black Text: #000000                       │
│   ████████████████████████████████████████  │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 🎯 Teams View (Carousel)

```
┌─────────────────────────────────────────────┐
│           🏀 WYOHOOPS LOGO 🏀               │
├─────────────────────────────────────────────┤
│  [Home] [Teams] [Rankings] [Players] [Stats]│
│          ^^^^^^                             │
├─────────────────────────────────────────────┤
│                                             │
│          ALL TEAMS (A-Z)                    │
│                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  │
│  │ 🏫        │  │ 🏫        │  │ 🏫        │  │
│  │ Arvada-  │  │ Big Horn │  │ Big Piney│  │
│  │ Clearmont│  │          │  │          │  │
│  │ 1A       │  │ 2A       │  │ 2A       │  │
│  │ 12-8 W-L │  │ 16-4 W-L │  │ 7-10 W-L │  │
│  └──────────┘  └──────────┘  └──────────┘  │
│                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  │
│  │ Buffalo  │  │ Burns    │  │ Campbell │  │
│  │ ...      │  │ ...      │  │ County   │  │
│  └──────────┘  └──────────┘  └──────────┘  │
│                                             │
│           [Click any team to view]          │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 📋 Team Detail View (Carousel)

```
┌─────────────────────────────────────────────┐
│           🏀 WYOHOOPS LOGO 🏀               │
├─────────────────────────────────────────────┤
│  [Home] [Teams] [Rankings] [Players] [Stats]│
│          ^^^^^^                             │
├─────────────────────────────────────────────┤
│                                             │
│  ← Back to Teams                            │
│                                             │
│  ┌───────────────────────────────────────┐  │
│  │  🏫 SHERIDAN BRONCS                   │  │
│  │                                       │  │
│  │  Classification: 4A                   │  │
│  │  Record: 14-1 (93.3%)                 │  │
│  │  Ranking: #1 in 4A                    │  │
│  │                                       │  │
│  │  📊 Ratings:                          │  │
│  │  Offensive:  98/100 ████████████░░    │  │
│  │  Defensive:  96/100 ███████████░░░    │  │
│  │  Overall:    97/100 ████████████░░    │  │
│  │                                       │  │
│  │  👥 Roster:                           │  │
│  │  - John Doe (Senior, G)               │  │
│  │  - Mike Smith (Junior, F)             │  │
│  │  - Tom Johnson (Sophomore, C)         │  │
│  │  ...                                  │  │
│  │                                       │  │
│  │  🏀 Recent Games:                     │  │
│  │  W 73-32 vs Campbell County           │  │
│  │  W 65-58 vs Thunder Basin             │  │
│  │  L 62-68 vs Rock Springs              │  │
│  │  ...                                  │  │
│  └───────────────────────────────────────┘  │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 🎮 Bulk Game Import Interface

```
┌─────────────────────────────────────────────┐
│     WyoHoops DB → Import/Tools              │
├─────────────────────────────────────────────┤
│                                             │
│  📥 Bulk Game Import                        │
│                                             │
│  Paste games in this format (one per line): │
│  shs73cchs32 = SHS won 73-32 vs CCHS        │
│                                             │
│  ┌───────────────────────────────────────┐  │
│  │ shs73cchs32                           │  │
│  │ tbhs65grhs58                          │  │
│  │ kwhs80nchs75                          │  │
│  │ lovhs88bhs50                          │  │
│  │ chs72dhs68                            │  │
│  │                                       │  │
│  │                                       │  │
│  │                                       │  │
│  └───────────────────────────────────────┘  │
│                                             │
│  [ Import Games ]                           │
│                                             │
│  ✅ Success! 5 games imported               │
│     - Sheridan: +1 win                      │
│     - Campbell County: +1 loss              │
│     - Thunder Basin: +1 win                 │
│     - Green River: +1 loss                  │
│     ...                                     │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 🎨 Carousel Animation

```
STATE 1: Home Screen Active
┌───────────────────────────────────────────────┐
│ [Home]  Teams  Rankings  Players  Stats      │
│  ^^^^                                         │
│                                               │
│ ┌─────────────────────────────────────────┐   │
│ │        HOME SCREEN (visible)            │   │
│ │        Top 5 Teams & Players            │   │
│ └─────────────────────────────────────────┘   │
│                                               │
└───────────────────────────────────────────────┘

User clicks "Teams" tab...

STATE 2: Transition (0.4s)
┌───────────────────────────────────────────────┐
│  Home  [Teams]  Rankings  Players  Stats     │
│         ^^^^^^                                │
│                                               │
│ ┌─────────────┐┌─────────────────────────┐   │
│ │    HOME     ││     TEAMS SCREEN        │   │
│ │  (sliding   ││     (sliding in         │   │
│ │   left)     ││      from right)        │   │
│ └─────────────┘└─────────────────────────┘   │
│  ◄──────────────                              │
└───────────────────────────────────────────────┘

STATE 3: Teams Screen Active
┌───────────────────────────────────────────────┐
│  Home  [Teams]  Rankings  Players  Stats     │
│         ^^^^^^                                │
│                                               │
│ ┌─────────────────────────────────────────┐   │
│ │     TEAMS SCREEN (visible)              │   │
│ │     All Teams A-Z                       │   │
│ └─────────────────────────────────────────┘   │
│                                               │
└───────────────────────────────────────────────┘
```

---

## 📱 Mobile Responsive

```
Desktop (1200px+):
┌─────────────────────────────────────────┐
│  Logo                                   │
│  [Home] [Teams] [Rankings] [Players]   │
│  ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐    │
│  │    │ │    │ │    │ │    │ │    │    │
│  │Team│ │Team│ │Team│ │Team│ │Team│    │
│  └────┘ └────┘ └────┘ └────┘ └────┘    │
└─────────────────────────────────────────┘

Tablet (768px+):
┌───────────────────────────────────┐
│  Logo                             │
│  [Home] [Teams] [Rankings]        │
│  ┌────┐ ┌────┐ ┌────┐             │
│  │    │ │    │ │    │             │
│  │Team│ │Team│ │Team│             │
│  └────┘ └────┘ └────┘             │
│  ┌────┐ ┌────┐                    │
│  │Team│ │Team│                    │
│  └────┘ └────┘                    │
└───────────────────────────────────┘

Mobile (< 768px):
┌───────────────────┐
│  Logo             │
│  [≡ Menu]         │
│  ┌──────────────┐ │
│  │              │ │
│  │    Team      │ │
│  │              │ │
│  └──────────────┘ │
│  ┌──────────────┐ │
│  │    Team      │ │
│  └──────────────┘ │
└───────────────────┘
```

---

## 🎯 Admin Backend

```
WordPress Admin Menu:

WyoHoops DB
├─ Teams ─────────── Manage all 69 teams
│   └─ [Edit] → Set ratings, colors, roster
│
├─ Games ─────────── Manage all games
│   └─ [Add] → Enter game details
│
├─ Players ───────── Manage player profiles
│   └─ [Add] → Create player profile
│
├─ Settings ──────── Customize plugin
│   ├─ Upload logo
│   ├─ Set background colors
│   └─ Configure options
│
└─ Import/Tools ──── Data management
    ├─ Import Default Teams
    ├─ Import Basketball Records
    └─ 📥 Bulk Game Import
        └─ [Paste games here]
```

---

## 🔍 Shortcode Format Examples

```
VALID FORMATS:

✅ shs73cchs32
   Sheridan 73, Campbell County 32
   
✅ tbhs65grhs58
   Thunder Basin 65, Green River 58
   
✅ lovhs88bhs50
   Lovell 88, Buffalo 50
   
✅ KWHS80NCHS75
   Kelly Walsh 80, Natrona County 75
   (case insensitive)

MULTI-GAME PASTE:
shs73cchs32
tbhs65grhs58
kwhs80nchs75
lovhs88bhs50
chs72dhs68
wihs92rmhs45
ghs55nhs48
```

---

## 🎨 Visual Effects

### Hover States
```
Card at Rest:
┌──────────────┐
│              │
│   Team Card  │
│              │
└──────────────┘

Card on Hover:
┌──────────────┐ ↑ (lifts up)
│▓▓▓▓▓▓▓▓▓▓▓▓▓▓│ (shadow increases)
│▓▓Team Card▓▓▓│ (gold border glows)
│▓▓▓▓▓▓▓▓▓▓▓▓▓▓│
└──────────────┘
```

### Button States
```
Normal:   [ Import Games ]
Hover:    [█Import Games█] (gold background)
Active:   [▒Import Games▒] (darker gold)
Loading:  [⟳ Importing...] (spinner animation)
```

### Tab Transitions
```
Tab Switch Animation:
[Active]──►[Next]
   0.4s smooth slide
   Gold underline moves
   Content fades in
```

---

## 📊 Statistics Display

```
Rating Meter:
Offensive: 98/100
[████████████████████░░] 98

Defensive: 85/100
[█████████████████░░░░░] 85

Overall: 92/100
[██████████████████░░░░] 92

Colors:
90-100: Gold (#FFD700)
75-89:  Light Gold (#FFE97D)
60-74:  Gray (#CCCCCC)
< 60:   Light Gray (#E0E0E0)
```

---

## ✅ Production Checklist

- ✅ Full-page layout
- ✅ Centered logo
- ✅ Metallic gold theme
- ✅ Light configurable backgrounds
- ✅ Home dashboard (top 5s)
- ✅ Bulk game import with shortcodes
- ✅ Carousel navigation
- ✅ All 69 team shortcodes
- ✅ Teams list (alphabetical)
- ✅ Team detail views
- ✅ Player portal
- ✅ Smooth animations (0.4s)
- ✅ Mobile responsive
- ✅ Touch-friendly
- ✅ Backend management
- ✅ Documented
- ✅ Production ready

---

## 🎉 Result

A **modern, high-tech data gallery** for Wyoming basketball with:

- Smooth carousel navigation between screens
- Bulk game import via simple text format
- Beautiful metallic gold design
- Complete backend management
- Mobile-friendly responsive layout
- Professional animations and transitions
- Easy-to-use interface

**Status: READY FOR DEPLOYMENT** 🏀
